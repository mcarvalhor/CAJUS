<?php

require_once("config.php");
require_once("auth.php");

$utils_https = NULL;
$utils_installPath = NULL;

$maxMemorySize = NULL;
$maxPostSize = NULL;
$maxUploadSize = NULL;

$utils_semaphore = NULL;
$utils_semaphoreAcquired = FALSE;

function disableCache() {
	header("Cache-Control: private, no-store, max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
}

function BASE__parseStrSize(string $size) {
	$unit = preg_replace("/[^bkmgtpezy]/i", "", $size);
	$size = preg_replace("/[^0-9\.]/", "", $size);
	if(empty($unit)) {
		return (int) round($size);
	} else {
		return (int) round($size * pow(1024, stripos("bkmgtpezy", $unit[0])));;
	}
}

function getMaxMemorySize() {
	/*
	* Esta função retorna o tamanho máximo que um script PHP pode utilizar na RAM.
	*
	* return: a quantidade em bytes (maior que 0), ou -1 caso ilimitado.
	*/
	global $maxMemorySize;
	if($maxMemorySize !== NULL) {
		return $maxMemorySize;
	}

	$maxMemorySize = -1;
	$maxMemory = BASE__parseStrSize(ini_get("memory_limit"));
	if($maxMemory > 0) {
		$maxMemorySize = $maxMemory;
	}

	return $maxMemorySize;
}

function getMaxPostSize() {
	/*
	* Esta função retorna o tamanho máximo de dados que podem ser submetidos via POST ao servidor.
	*
	* return: a quantidade em bytes (maior que 0), ou -1 caso ilimitado.
	*/
	global $maxPostSize;
	if($maxPostSize !== NULL) {
		return $maxPostSize;
	}

	$maxPostSize = -1;
	$maxPost = BASE__parseStrSize(ini_get("post_max_size"));
	if($maxPost > 0) {
		$maxPostSize = $maxPost;
	}

	$maxMemory = getMaxMemorySize();
	if($maxMemory > 0 && $maxMemory < $maxPostSize) {
		$maxPostSize = $maxMemory;
	}

	return $maxPostSize;
}

function getMaxUploadSize() {
	/*
	* Esta função retorna o tamanho máximo de um arquivo que pode ser carregado no servidor.
	*
	* return: a quantidade em bytes (maior que 0), ou -1 caso ilimitado.
	*/
	global $maxUploadSize;
	if($maxUploadSize !== NULL) {
		return $maxUploadSize;
	}

	$maxUploadSize = -1;
	$maxUpload = BASE__parseStrSize(ini_get("upload_max_filesize"));
	if($maxUpload > 0) {
		$maxUploadSize = $maxUpload;
	}

	$maxPost = getMaxPostSize();
	if($maxPost > 0 && $maxPost < $maxUploadSize) {
		$maxUploadSize = $maxPost;
	}

	if(CNF_MAXFILEUPLOADSIZE >= 0 && CNF_MAXFILEUPLOADSIZE < $maxUploadSize) {
		$maxUploadSize = CNF_MAXFILEUPLOADSIZE;
	}

	return $maxUploadSize;
}

function bytesToSize(int $bytes, array $units = [0 => "%.2f bytes", 1 => "%.2f byte", 2 => "%.2f bytes", 1024 => "%.2f KiB", 1048576 => "%.2f MiB", 1073741824 => "%.2f GiB"]){
	/*
	* Converte um número de bytes para uma cadeia de texto organizada.
	*
	* $bytes: número de bytes.
	* $units: valores e suas respectivas unidades (começa em 0). Lembre-se de incluir um e apenas um modificador para ponto flutuante (%f).
	* return: string com os bytes devidamente convertidos.
	*/
	if($bytes < 0 || empty($units) || $units[0] === NULL) {
		return FALSE;
	}
	$zeroUnit = $units[0];
	unset($units[0]);
	$keys = array_keys($units);
	rsort($keys);
	foreach($keys as $key) {
		if($bytes / (double) $key < 1) {
			continue;
		}
		return sprintf($units[$key], $bytes / (double) $key);
	}
	return sprintf($zeroUnit, $bytes);
}

function parseForDOM($content) {
	/*
	* Essa função converte caracteres especials (como < ou aspas) para exibição adequada no HTML.
	* Além disso, adiciona "<br>" antes de quaisquer pulos de linha.
	*
	* $content: texto que precisa dessa conversão.
	* return: $content adequadamente convertido.
	*/
	if(empty($content) || gettype($content) !== "string") {
		$content = (string) $content;
	}
	return nl2br(htmlentities($content, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"));
}

function parseForHTML($content) {
	/*
	* Essa função converte caracteres especials (como < ou aspas) para exibição adequada no HTML.
	*
	* $content: texto que precisa dessa conversão.
	* return: $content adequadamente convertido.
	*/
	if(empty($content) || gettype($content) !== "string") {
		$content = (string) $content;
	}
	return htmlentities($content, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function getNonce(string $type = "general", bool $unique = FALSE, int $limit = 1) {
	/**
	 * Essa função cria um nonce (chave), usado para evitar ataques CSRF.
	 *
	 * $type: opcional que identifica o tipo de requisição (login, confirmar compra, ...).
	 * $unique: TRUE para invalidar outros nonces criados e permitir apenas um para validação, FALSE caso contrário.
	 * $limit: quantas vezes um único nonce pode ser usado.
	 * return: FALSE em caso de erros, ou o novo nonce (string) criado.
	 */
	if(empty($type) || $limit < 1) {
		return FALSE;
	}
	if(!startSession()) {
		return FALSE;
	}
	$newNonce = uniqid(time() . "_", true);
	if($unique || empty($_SESSION["nonce"][$type]) || count($_SESSION["nonce"][$type]) >= 128) {
		$_SESSION["nonce"][$type] = [ ];
	}
	$_SESSION["nonce"][$type][$newNonce] = $limit;
	return $newNonce;
}

function checkNonce($nonce, string $type = "general", bool $consume = TRUE) {
	/**
	 * Essa função verifica um nonce (chave), usado para evitar ataques CSRF.
	 *
	 * $nonce: nonce recebido pelo cliente, ao qual deseja-se validar.
	 * $type: opcional que identifica o tipo de requisição (login, confirmar compra, ...), que deve ser o mesmo de criação do nonce.
	 * return: FALSE em caso de nonce inválido ou erros, ou o novo nonce (string) criado.
	 */
	if(empty($nonce) || gettype($nonce) !== "string" || empty($type)) {
		return FALSE;
	}
	if(!startSession()) {
		return FALSE;
	}
	if(empty($_SESSION["nonce"][$type])) {
		return FALSE;
	}
	if(empty($_SESSION["nonce"][$type][$nonce])) {
		return FALSE;
	}
	if(!$consume) {
		return TRUE;
	}
	$_SESSION["nonce"][$type][$nonce]--;
	if($_SESSION["nonce"][$type][$nonce] < 1) {
		unset($_SESSION["nonce"][$type][$nonce]);
	}
	return TRUE;
}

function getCaptcha(string $nonce) {
	/**
	 * Essa função cria um captcha, usado para evitar ataques brute-force.
	 *
	 * $nonce: nonce da sessão do usuário.
	 * return: FALSE em caso de erros, ou o texto do captcha caso contrário.
	 */
	if(empty($nonce)) {
		return FALSE;
	}
	if(!startSession()) {
		return FALSE;
	}
	$newCaptcha = getRandomString(4, 8, array_merge(range('A', 'N'), range('P', 'Z'), range('a', 'n'), range('p', 'z'), range('2', '9')));
	$newSeed = mt_rand();
	if(empty($_SESSION["captcha"]) || count($_SESSION["captcha"]) >= 128) {
		$_SESSION["captcha"] = [ ];
	}
	$_SESSION["captcha"][$nonce] = [ "text" => $newCaptcha, "seed" => $newSeed ];
	return $newCaptcha;
}

function checkCaptcha($text, $nonce, bool $consume = TRUE) {
	/**
	 * Essa função verifica um captcha, usado para evitar ataques brute-force.
	 *
	 * $text: texto recebido para o captcha pelo usuário.
	 * $nonce: nonce da sessão do usuário em que o captcha foi criado.
	 * $consume: opcional que indica se deve ser apenas verificado (FALSE) ou verificado e invalidado (TRUE).
	 * return: FALSE em caso de nonce inválido ou erros, ou TRUE caso contrário.
	 */
	if(empty($text) || gettype($text) !== "string" || empty($nonce) || gettype($nonce) !== "string") {
		return FALSE;
	}
	if(!startSession()) {
		return FALSE;
	}
	if(empty($_SESSION["captcha"])) {
		return FALSE;
	}
	if(empty($_SESSION["captcha"][$nonce])) {
		return FALSE;
	}
	$captchaText = $_SESSION["captcha"][$nonce]["text"];
	if($consume) {
		unset($_SESSION["captcha"][$nonce]);
	}
	if(strtolower($captchaText) !== strtolower($text)) {
		return FALSE;
	}
	return TRUE;
}

function getRandomString(int $minLength = 8, int $maxLength = 64, array $domain = [ ]) {
	/*
	* Esta função retorna uma cadeia de caracteres aleatória formada por um determinado domínio.
	*
	* $minLength: número de caracteres mínimo. Deve ser estar entre 1 e $maxLength.
	* $maxLength: número de caracteres máximo. Deve ser maior ou igual a $minLength.
	* $domain: array indexada com domínio de caracteres usado. Os elementos só podem ser do tipo "string" de tamanho 1.
	* return: FALSE em caso de erros, ou a cadeia de caracteres gerada em caso de sucesso.
	*/
	if($minLength < 1 || $maxLength < $minLength) {
		return FALSE;
	}
	if(empty($domain)) {
		$domain = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
	} else {
		if(array_keys($domain) !== range(0, count($domain) - 1)) {
			return FALSE;
		}
		foreach($domain as $value) {
			$value = (string) $value;
			if(mb_strlen($value, "UTF-8") !== 1) {
				return FALSE;
			}
		}
	}
	$length = mt_rand($minLength, $maxLength);
	$n = count($domain) - 1;
	$output = "";
	for($i = 0; $i < $length; $i++) {
		$output .= (string) $domain[mt_rand(0, $n)];
	}
	return $output;
}

function mutexLock() {
	/*
	* Bloqueia o script atual para execução enquanto o semáforo de sincronização Mutex não for obtido.
	*
	* return: TRUE em caso de sucesso, ou FALSE em caso de erros
	*/
	global $utils_semaphore, $utils_semaphoreAcquired;
	if($utils_semaphore === NULL) {
		$semaphoreKey = ftok(__DIR__ . DIRECTORY_SEPARATOR . CNS_SEMAPHORE, "G");
		if($semaphoreKey === -1) {
			return FALSE;
		}
		$semaphoreValue = sem_get($semaphoreKey, 1);
		if($semaphoreValue === FALSE) {
			return FALSE;
		}
		$utils_semaphore = $semaphoreValue;
		$utils_semaphoreAcquired = FALSE;
	}
	if($utils_semaphoreAcquired) {
		return FALSE;
	}
	$utils_semaphoreAcquired = sem_acquire($semaphore, FALSE);
	return $utils_semaphoreAcquired;
}

function mutexRelease() {
	/*
	* Libera o semáforo de sincronização Mutex para ser utilizado por outro script.
	*
	* return: TRUE em caso de sucesso, ou FALSE em caso de erros
	*/
	global $utils_semaphore, $utils_semaphoreAcquired;
	if(!$utils_semaphoreAcquired || $utils_semaphore === NULL) {
		return FALSE;
	}
	if(sem_release($utils_semaphore)) {
		$utils_semaphoreAcquired = FALSE;
		return TRUE;
	}
	return FALSE;
}

function isHttps() {
	global $utils_https;
	if($utils_https !== NULL) {
		return $utils_https;
	}
	if(!empty($_SERVER["HTTPS"])) {
		$utils_https = TRUE;
	} else {
		$utils_https = FALSE;
	}
	return $utils_https;
}

function getCurrentWebPath(int $subdirectories = 0) {
	global $utils_installPath;
	if($utils_installPath !== NULL) {
		return $utils_installPath;
	}
	if(!empty($_SERVER["SERVER_PORT"]) && !empty($_SERVER["HTTP_HOST"])) {
		$utils_installPath = (isHttps()) ? "https://" : "http://";
		$utils_installPath .= $_SERVER["HTTP_HOST"];
		/*if((!isHttps() && $_SERVER["SERVER_PORT"] != "80") || (isHttps() && $_SERVER["SERVER_PORT"] != "443")) {
			$utils_installPath .= ":" . $_SERVER["SERVER_PORT"];
		}*/
	} else {
		$utils_installPath = "";
	}
	$selfPath = $_SERVER["PHP_SELF"];
	for($i = 0; $i <= $subdirectories; $i++) {
		$selfPath = preg_replace("/^(.*)\/[^\/]*$/", "$1", $selfPath);
	}
	$selfPath .= "/";
	$utils_installPath .= $selfPath;
	return $utils_installPath;
}
