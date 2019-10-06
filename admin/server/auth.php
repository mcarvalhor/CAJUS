<?php

require_once("config.php");
require_once("db.php");

$loginSession = NULL;



function startSession() {
	/*
	* Essa função inicia a sessão de autenticação de um usuário.
	*
	* return: FALSE em caso de erros, os dados da sessão em caso de sucesso.
	*/
	global $loginSession;
	if($loginSession) {
		return $loginSession;
	}
	session_name(CNS_SESSIONNAME);
	if(!session_start()) {
		return FALSE;
	}

	$token = $_SESSION["auth"];
	if(empty($token)) {
		$token = $_COOKIE["saved_auth"];
		$_SESSION["auth"] = $_COOKIE["saved_auth"];
	}

	if(!empty($token)) {
		dbQuery("DELETE FROM " . DB_PREFIX . "auth_sessions WHERE expires <= ?;", [ time() ]);
		$authData = dbFetch("SELECT id FROM " . DB_PREFIX . "auth_sessions WHERE token=? LIMIT 1;", [ $_SESSION["auth"] ]);
		if(!empty($authData["id"])) {
			$loginSession = [
				"loggedIn" => true
			];
			return $loginSession;
		}
	}
	$loginSession = [ "loggedIn" => false ];

	return $loginSession;
}

function isLoggedIn() {
	/*
	* Essa função determina se há um usuário logado no momento.
	*
	* return: TRUE se está logado, FALSE se não está logado ou algo de errado ocorreu.
	*/
	global $loginSession;
	if(!startSession()) {
		return FALSE;
	}
	return $loginSession["loggedIn"];
}

function login($passwd, bool $keepLoggedIn = FALSE) {
	/*
	* Essa função cria uma sessão de login.
	*
	* $passwd: senha para autenticação.
	* return: FALSE em caso de erros, TRUE caso contrário.
	*/
	global $loginSession;
	if(!startSession()) {
		return FALSE;
	}
	if(empty($passwd) || gettype($passwd) !== "string") {
		return FALSE;
	}

	if(!password_verify($passwd, CNF_DEFAULTPASSWORD)) {
		return FALSE;
	}

	$token = uniqid(time() . "_", TRUE);
	$expires = time() + CNF_MAXSESSIONTIME;
	$_SESSION["auth"] = $token;
	$loginSession = [
		"loggedIn" => true
	];

	if($keepLoggedIn && CNF_SECURECOOKIE) {
		setcookie("saved_auth", $token, $expires, "", "", TRUE, TRUE); // Cookie seguro de validade 1 ano.
	} else if($keepLoggedIn && !CNF_SECURECOOKIE) {
		setcookie("saved_auth", $token, $expires, "", "", FALSE, TRUE); // Cookie de validade 1 ano.
	}

	if(dbQuery("INSERT INTO " . DB_PREFIX . "auth_sessions(token, expires) VALUES (?, ?);", [ $token, $expires ]) === FALSE) {
		return FALSE;
	}

	return TRUE;
}

function logout() {
	global $loginSession;
	if(!startSession()) {
		return FALSE;
	}
	if(!isLoggedIn()) {
		return TRUE;
	}
	dbQuery("DELETE FROM " . DB_PREFIX . "auth_sessions WHERE token=?;", [ $_SESSION["auth"] ]);
	$_SESSION["auth"] = NULL;
	unset($_SESSION["auth"]);
	unset($_COOKIE["saved_auth"]);
	setcookie("saved_auth", "", time() - 3600);
	$loginSession = [ "loggedIn" => false ];
	return TRUE;
}


