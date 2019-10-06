<?php

require_once("config.php");

$db = NULL;



function dbConnect() {
	/*
	* Essa função conecta ao banco de dados.
	* Ela não gera múltiplas conexões.
	*
	* return: FALSE em caso de erros, ou o objeto PDO caso nenhum erro ocorra.
	*/
	global $db;
	if($db) {
		return $db;
	}

	try {
		$dbCon = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
	} catch(PDOException $exc) {
		return FALSE;
	}

	$db = $dbCon;
	return $db;
}

function dbDisconnect() {
	/*
	* Essa função fecha conexão ao banco de dados (caso essa exista).
	*/
	global $db;
	$db = NULL; // Só isso é o bastante no PDO.
}



function dbQuery(string $query, array $params = NULL) {
	/*
	* Essa função executa uma query no banco de dados.
	*
	* $query: query SQL.
	* $params: array com parametros da query (a chave contém os modificadores).
	* return: FALSE em caso de erros, ou o objeto PDOStatement caso nenhum erro ocorra.
	*/
	global $db;
	if(empty($query)) {
		return FALSE;
	}
	if(!dbConnect()) {
		return FALSE;
	}

	// Caso em que nenhum parâmetro é passado para a SQL query.
	try {
		if(empty($params)) {
			$dbQuery = $db->query($query);
			if ($dbQuery) {
				return $dbQuery;
			}
			return FALSE;
		}
	} catch(PDOException $exc) {
		return FALSE;
	}
	
	// Caso em que são passados parâmetros para a SQL query.
	try {
		$st = $db->prepare($query);
		if(!$st) {
			return FALSE;
		}
		if(!$st->execute($params)) {
			return FALSE;
		}
	} catch(PDOException $exc) {
		return FALSE;
	}

	return $st;
}

function dbFetch(string $query, array $params = NULL) {
	/*
	* Essa função executa uma query no banco de dados e faz o fetching de possíveis dados resultantes (apenas o primeiro).
	*
	* $query: query SQL.
	* $params: array com parametros da query (a chave contém os modificadores).
	* return: FALSE em caso de erros, ou uma array com os resultados caso nenhum erro ocorra.
	*/
	$query = dbQuery($query, $params);
	if($query === FALSE) {
		return FALSE;
	}

	return $query->fetch();
}

function dbFetchAll(string $query, array $params = NULL) {
	/*
	* Essa função executa uma query no banco de dados e faz o fetching de possíveis dados resultantes (tudo).
	*
	* $query: query SQL.
	* $params: array com parametros da query (a chave contém os modificadores).
	* return: FALSE em caso de erros, ou uma array com os resultados caso nenhum erro ocorra.
	*/
	$query = dbQuery($query, $params);
	if($query === FALSE) {
		return FALSE;
	}

	return $query->fetchAll();
}

function dbFetchLOB(string $query, array $params = NULL, array $columnBind = NULL, array $columnBindLOB = NULL) {
	/*
	* Essa função executa uma query no banco de dados e faz o fetching de possíveis dados resultantes (apenas o primeiro).
	* Além disso, caso algum dos dados seja muito longo para ser carregado todo na memória RAM, é criado um ponteiro para leitura (LOB).
	*
	* Exemplo de chamada:
	* 	dbFetchLOB("SELECT id, large_upload, email FROM table WHERE email = ? LIMIT 1;", ["example@example.com"], [0 => &$id, 2 => &$email], [1 => &$upload]);
	* 	fpassthru($upload);
	*
	* $query: query SQL.
	* $params: array com parametros da query (a chave contém os modificadores).
	* $columnBind: array com os índices das colunas que devem ser parametrizadas (começando em 0) e o endereço das variáveis como valores.
	* return: FALSE em caso de erros, ou uma array com os resultados caso nenhum erro ocorra.
	*/
	$query = dbQuery($query, $params);
	if($query === FALSE) {
		return FALSE;
	}

	if(!empty($columnBind)) {
		foreach($columnBind as $column => &$value) {
			$query->bindColumn($column + 1, $value);
		}
	}

	if(!empty($columnBindLOB)) {
		foreach($columnBindLOB as $column => &$value) {
			$query->bindColumn($column + 1, $value, PDO::PARAM_LOB);
		}
	}

	$fetch = $query->fetch(PDO::FETCH_BOUND);

	if($fetch !== FALSE && !empty($columnBindLOB)) { // TODO: not returning a LOB resource, returning string instead (SQLite). Why?
		foreach($columnBindLOB as $column => &$value) {
			if(is_string($value)) {
				$tmpStream = fopen("php://temp", "rb+");
				fwrite($tmpStream, $value);
				rewind($tmpStream);
				$value = $tmpStream;
			}
			$query->bindColumn($column + 1, $value, PDO::PARAM_LOB);
		}
	}
	
	return $fetch;
}

function dbCount(string $query, array $params = NULL) {
	/*
	* Essa função executa uma query no banco de dados e faz a contagem de linhas acessadas.
	* Nota: pode não funcionar com SQLite.
	*
	* $query: query SQL.
	* $params: array com parametros da query (a chave contém os modificadores).
	* return: FALSE em caso de erros, ou um inteiro caso nenhum erro ocorra.
	*/
	$query = dbQuery($query, $params);
	if($query === FALSE) {
		return FALSE;
	}

	return $query->rowCount();
}

