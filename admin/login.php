<?php

require_once("server/autoload.php");
textdomain("admin");
disableCache();
startSession();

if(isLoggedIn()) {
	header("Location: index.php");
	exit(0);
}


$error = FALSE;

if($_GET["action"] == "login") {
	if(!checkNonce($_POST["nonce"], "login")) {
		$error = gettext('Session expired. Try again.');
	} else if(empty($_POST["password"])) {
		$error = gettext('Enter the password.');
	} else if(!checkCaptcha($_POST["captcha"], $_POST["nonce"])) {
		$error = gettext('Please, enter the characters you see in the image.');
		http_response_code(403);
	} else if(!login($_POST["password"], (($_POST["save"] == "true") ? TRUE : FALSE))) {
		$error = gettext('Wrong password. Try again.');
	} else {
		header("Location: index.php");
		exit(0);
	}
} else if($_GET["action"] == "captcha") {
	$nonce = $_GET["nonce"];
	if(empty($nonce) || empty($_SESSION["captcha"][$nonce])) {
		echo gettext('Invalid request.');
		http_response_code(403);
		exit(0);
	}
	$text = $_SESSION["captcha"][$nonce]["text"];
	$seed = $_SESSION["captcha"][$nonce]["seed"];
	if(!function_exists("imagecreate")) {
		echo gettext('PHP required extension not installed.');
		http_response_code(500);
		exit(0);
	}
	// Criar imagem.
	$width = 300;
	$height = 100;
	$fontSize = 25;
	$fontFile = "./server/UbuntuMono-Regular.ttf";
	$imgBuilder = imagecreate($width, $height);
	// Ativar semente única para imagem.
	mt_srand($seed);
	// Define cores
	$imgBg = imagecolorallocate($imgBuilder, 255, 255, 255);
	$imgFg = imagecolorallocate($imgBuilder, 0, 0, 0);
	$rand_float = function($min, $max) {
		return ($max - $min) * (mt_rand() / mt_getrandmax()) + $min;
	};
	// Preenche com o plano de fundo.
	imagefill($imgBuilder, 0, 0, $imgBg);
	// Escreve a string do texto.
	$x = $rand_float($fontSize*0.25, $fontSize*0.75);
	$y = $rand_float($fontSize + $fontSize/2, $height - $fontSize/2);
	for($i = 0; $i < strlen($text); $i++) {
		$angle = $rand_float(-30, +30);
		imagettftext($imgBuilder, $fontSize, $angle, $x + $rand_float(-5, +5), $y + $rand_float(-$fontSize*0.25, $fontSize*0.25), $imgFg, $fontFile, $text[$i]);
		$x += ($width - $fontSize)/strlen($text);
	}
	// Desenha linhas/retângulos/elipses/arcos na imagem, de forma a dificultar captcha.
	$nLines = mt_rand(3, 6);
	for($i = 0; $i < $nLines; $i++) {
		switch(rand(0, 3)) {
			case 0: // Line.
				$x1 = $rand_float($fontSize*0.25, $fontSize*0.75);
				$y1 = $y + $rand_float(-$fontSize, $fontSize*0.25);
				$x2 = $rand_float($width - $fontSize*0.75, $width - $fontSize*0.25);
				$y2 = $y + $rand_float(-$fontSize, $fontSize*0.25);
				imageline($imgBuilder, $x1, $y1, $x2, $y2, $imgFg);
			break;
			case 1: // Rectangle.
				$x1 = $rand_float($fontSize*0.75, $width - $fontSize*0.25);
				$y1 = $y + $rand_float(-$fontSize, $fontSize*0.25);
				$x2 = $rand_float($fontSize*0.75, $width - $fontSize*0.25);
				$y2 = $y + $rand_float(-$fontSize, $fontSize*0.25);
				imagerectangle($imgBuilder, $x1, $y1, $x2, $y2, $imgFg);
			break;
			case 2: // Ellipse.
				$x1 = $rand_float($fontSize*0.75, $width - $fontSize*0.25);
				$y1 = $y + $rand_float(-$fontSize, $fontSize*0.25);
				$w1 = $rand_float($width*0.125, $width*0.75);
				$h1 = $rand_float($height*0.125, $height*0.75);
				imageellipse($imgBuilder, $x1, $y1, $w1, $h1, $imgFg);
			break;
			case 3: // Arc.
				$x1 = $rand_float($fontSize*0.75, $width - $fontSize*0.25);
				$y1 = $y + $rand_float(-$fontSize, $fontSize*0.25);
				$w1 = $rand_float($width*0.125, $width*0.75);
				$h1 = $rand_float($height*0.125, $height*0.75);
				$a1 = $rand_float(0, 360);
				$a2 = $a1 + $rand_float(60, 180);
				imagearc($imgBuilder, $x1, $y1, $w1, $h1, $a1, $a2, $imgFg);
			break;
		}
	}
	// Retorna conteúdo da imagem.
	header("Content-Type: image/png");
	imagepng($imgBuilder);
	imagedestroy($imgBuilder);
	exit(0);
}

$newNonce = getNonce("login");
getCaptcha($newNonce);

?><!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,height=device-height,initial-scale=1,user-scalable=no">
	<meta name="robots" content="noindex,nofollow,noarchive,noodp,noydir">
	<title>CAJUS</title>
	<style type="text/css">
		@media not all and (min-width: 360px) and (min-height: 360px){
			#main-noscreen {
				display: block;
			}
			#main {
				display: none;
			}
		}
		@media all and (min-width: 360px) and (min-height: 360px){
			#main-noscreen {
				display: none;
			}
			#main {
				display: block;
			}
		}
		body {
			display: block;
			background-color: rgb(0, 0, 0);
			color: rgb(0, 0, 0);
		}
		#main {
			font-size: 16px;
			margin: 100px auto;
			width: 315px;
			padding: 15px;
			border-radius: 10px;
			background-color: rgb(255, 255, 255);
			box-shadow: 0px 0px 20px 10px rgb(255, 255, 255);
			overflow: hidden;
		}
		#header {
			display: block;
			color: rgb(84, 38, 128);
			text-align: center;
			margin-bottom: 50px;
		}
		#header a {
			color: rgb(84, 38, 128);
			font-size: 2em;
			margin: 0px auto;
			text-align: center;
			text-decoration: none;
		}
		#loginInfo {
			display: block;
			text-align: justify;
			margin-bottom: 25px;
		}
		#loginError {
			display: block;
			color: rgb(255, 0, 0);
			text-align: center;
			margin-bottom: 25px;
		}
		#loginError a {
			color: rgb(255, 0, 0);
		}
		#loginForm {
			display: block;
			margin-bottom: 50px;
		}
		#loginForm .field {
			display: block;
			margin-top: 0px;
			margin-left: auto;
			margin-bottom: 25px;
			margin-right: auto;
			vertical-align: middle;
		}
		#loginForm .field.text input, #loginForm .field.captcha input {
			display: block;
			border-style: solid;
			border-width: 0px 0px 2px 0px;
			border-color: rgba(0, 0, 0, 0.25);
			outline-width: 0px;
			margin: 0px auto;
			padding: 5px;
			box-sizing: border-box;
			width: 100%;
			transition: border-color 0.25s;
		}
		#loginForm .field.text input:hover, #loginForm .field.captcha input:hover {
			border-color: rgba(0, 0, 0, 1);
		}
		#loginForm .field.text input:focus, #loginForm .field.captcha input:focus {
			border-color: rgba(132, 0, 255, 1);
		}
		#loginForm .field.captcha img {
			display: block;
			margin: 5px auto;
		}
		#loginForm .field.captcha input {
			display: inline-block;
			width: 270px;
		}
		#loginForm .field.captcha button {
			display: inline-block;
			margin-left: 5px;
			padding: 2px;
			border-style: solid;
			border-width: 2px;
			border-color: rgb(0, 0, 0);
			cursor: pointer;
			color: rgb(255, 255, 255);
			border-radius: 12px;
			background-color: rgb(132, 0, 255);
			transition: background-color,color 0.25s,0.25s;
			width: 25px;
		}
		#loginForm .field.buttons button {
			display: inline-block;
			margin: 2px;
			padding: 10px;
			border-style: solid;
			border-width: 2px;
			border-color: rgb(0, 0, 0);
			cursor: pointer;
			color: rgb(255, 255, 255);
			background-color: rgb(132, 0, 255);
			transition: background-color,color 0.25s,0.25s;
		}
		#loginForm .field.buttons button:hover, #loginForm .field.captcha button:hover {
			color: rgb(255, 255, 255);
			background-color: rgb(84, 38, 128);
		}
		#loginForm .field.buttons button:first-child {
			margin-left: 0px;
		}
		#loginForm .field.buttons button:last-child {
			margin-right: 0px;
		}
		#loginForm .field.buttons button.icon {
			display: inline-block;
			width: 30px;
			height: 30px;
			padding: 8px;
			background-image: url("resources/loginIcons.png");
			background-repeat: no-repeat;
			background-size: 26px auto;
		}
		#loginForm .field a {
			display: inline-block;
			color: rgb(0, 0, 0);
			transition: color 0.25s;
		}
		#loginForm .field a:hover {
			color: rgb(84, 38, 128);
		}
		#footer {
			display: block;
			font-size: 0.75em;
			text-align: justify;
		}
		#footer .center {
			text-align: center;
		}
		#footer a {
			color: rgb(0, 0, 0);
			transition: color 0.25s;
		}
		#footer a:hover {
			color: rgb(84, 38, 128);
		}
		#main-noscreen {
			margin: 3px;
			color: rgb(255, 255, 255);
			text-align: justify;
		}
	</style>
</head>

<body>
	<div id="main">
		<div id="header">
			<a href="?">CAJUS</a>
			<p><?php echo gettext("CAJUS Ain't Just a URL Shortener"); ?></p>
		</div>
		<div id="loginInfo">
			<p><?php echo gettext('This area is restricted to administrators. Enter the password to continue.'); ?></p>
		</div>
		<?php
			if($error) {
				echo '<div id="loginError"><p>' . $error . '</p></div>';
			}
		?>
		<div id="loginForm">
			<form action="?action=login" method="post">
				<input type="hidden" name="nonce" value="<?php echo $newNonce; ?>">
				<div class="field text">
					<input type="password" name="password" placeholder="<?php echo gettext('Password'); ?>" required="required">
				</div>
				<div class="field captcha">
					<label for="form-captcha"><?php echo gettext("Type the text you see in the image below:"); ?></label>
					<img src="login.php?action=captcha&nonce=<?php echo $newNonce; ?>" alt="">
					<input type="text" name="captcha" id="form-captcha" placeholder="<?php echo gettext("(type the above characters)"); ?>" required="required">
					<button type="submit" style="display: none;"></button>
					<button type="submit" formaction="?" formnovalidate="formnovalidate" title="<?php echo gettext("Generate new image"); ?>">&#8635;</button>
				</div>
				<div class="field buttons">
					<button type="submit"><?php echo gettext("Login"); ?></button>
				</div>
				<div class="field">
					<p><label><input type="checkbox" name="save" value="true"> <?php echo gettext("Keep me logged in"); ?></label></p>
					<p><a href="forgotPassword.php"><?php echo gettext("I forgot my password"); ?></a></p>
				</div>
			</form>
		</div>
		<div id="footer">
			<p class="center"><?php echo gettext('No personal data is stored by SlugsManager.'); ?></p>
		</div>
	</div>
	<div id="main-noscreen">
		<p><?php echo gettext("Your screen is too small to show this webpage."); ?></p>
	</div>
</body>

</html>
