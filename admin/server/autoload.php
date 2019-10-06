<?php

if(!file_exists(__DIR__ . DIRECTORY_SEPARATOR . "config.php")) {
	header("Location: indexSetup.php");
	exit(0);
}

require_once("config.php");
require_once("consts.php");
require_once("db.php");
require_once("auth.php");
require_once("utils.php");



// Bind translation locale folder
bindtextdomain("error", __DIR__ . DIRECTORY_SEPARATOR . "locale");
bindtextdomain("admin", __DIR__ . DIRECTORY_SEPARATOR . "locale");


