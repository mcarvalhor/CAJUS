<?php

require_once("server/autoload.php");
disableCache();
startSession();

if(!isLoggedIn()) {
	header("Location: login.php");
	exit(0);
}

$nonce = $_GET["nonce"];

if(empty($nonce)) {
	header("Location: index.php");
	exit(0);
}

if(!checkNonce($nonce, "logout")) {
	header("Location: index.php");
	exit(0);
}


logout();
header("Location: login.php");
