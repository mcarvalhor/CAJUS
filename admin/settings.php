<?php

require_once("server/autoload.php");
textdomain("admin");
disableCache();
startSession();

if(!isLoggedIn()) {
	header("Location: login.php");
	exit(0);
}


$showForm = TRUE;
$url = dbFetch("SELECT id, content FROM " . DB_PREFIX . "slugs WHERE LOWER(name) = LOWER(?);", [ "index" ]);
if(empty($url) || empty($url["id"])) {
	$url = "";
} else {
	$url = $url["content"];
}

if($_POST["action"] == "save-index") {
	$url = $_POST["url"];
	if(!checkNonce($_POST["nonce"], "save-index")) {
		$error = gettext('Session expired. Try again.');
	} else if(!empty($url) && strlen($url) > CNS_MAXURLLENGTH) {
		$error = sprintf(gettext("The URL you entered is too long. Enter something smaller (maximum %d characters)."), CNS_MAXURLLENGTH);
	} else if(!empty($url) && filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
		$error = gettext("You haven't entered a valid URL. Don't forget the protocol (http, https, ...).");
	} else {
		dbQuery("DELETE FROM " . DB_PREFIX . "slugs WHERE LOWER(name) = LOWER(?);", [ "index" ]);
		if(!empty($url)) {
			dbQuery("INSERT INTO " . DB_PREFIX . "slugs(name, type, content, mime, extension, bot_index, access_count) VALUES(?, ?, ?, ?, ?, ?, ?);",
				[ "index", "redirect", $url, NULL, NULL, 0, NULL ]);
		}
		header("Location: ?");
		exit(0);
	}
}

$newNonce = getNonce("save-index");
$logoutNonce = getNonce("logout");

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
		#header p a {
			display: inline-block;
			margin-right: 5px;
			font-size: 1.2em;
		}
		#error {
			margin-top: 50px;
			color: rgb(255, 0, 0);
		}
		#form {
			display: block;
			margin: 50px 0px;
		}
		#form .field {
			display: block;
			margin-top: 0px;
			margin-left: auto;
			margin-bottom: 25px;
			margin-right: auto;
			vertical-align: middle;
		}
		#form .field.text input {
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
		#form .field.text input:hover {
			border-color: rgba(0, 0, 0, 1);
		}
		#form .field.text input:focus {
			border-color: rgba(132, 0, 255, 1);
		}
		#form .field.buttons button {
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
		#form .field.buttons button:hover {
			color: rgb(255, 255, 255);
			background-color: rgb(84, 38, 128);
		}
		#form .field.buttons button:first-child {
			margin-left: 0px;
		}
		#form .field.buttons button:last-child {
			margin-right: 0px;
		}
		#form .field .small {
			font-size: 0.75em;
		}
		#form .field a {
			display: inline-block;
			color: rgb(0, 0, 0);
			transition: color 0.25s;
		}
		#form .field a:hover {
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
			<p><a href="index.php" title="<?php echo gettext("Go back"); ?>">&#8592;</a><?php echo gettext("CAJUS Ain't Just a URL Shortener"); ?></p>
		</div>
		<?php
			if($error) {
				echo '<div id="error"><p>' . $error . '</p></div>';
			}
		?>
		<?php
			if($showForm) {
		?>
		<div id="form">
			<form action="?" method="post" onreset="window.location.href = 'index.php';">
				<input type="hidden" name="action" value="save-index">
				<input type="hidden" name="nonce" value="<?php echo $newNonce; ?>">
				<div class="field text">
					<input type="text" name="url" maxlength="<?php echo CNS_MAXURLLENGTH; ?>" placeholder="(<?php echo gettext("Home page redirect URL"); ?>)" value="<?php echo parseForHTML($url); ?>" autofocus="autofocus">
					<p class="small"><?php echo gettext("You can leave it blank if you don't want to redirect users to your home page."); ?></p>
				</div>
				<div class="field buttons">
					<button type="submit"><?php echo gettext("Save"); ?> &#187;</button>
					<button type="reset"><?php echo gettext("Done"); ?> &#215;</button>
				</div>
			</form>
		</div>
		<?php
			}
		?>
		<div id="footer">
			<p class="center"><?php echo gettext("Logged-in"); ?> (<a href="logout.php?nonce=<?php echo $logoutNonce; ?>"><?php echo gettext("Logout"); ?></a>)</p>
		</div>
	</div>
	<div id="main-noscreen">
		<p><?php echo gettext("Your screen is too small to show this webpage."); ?></p>
	</div>
</body>

</html>
