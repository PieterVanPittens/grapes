<?php

function notAuthorized() {
	//header('WWW-Authenticate: Basic realm="Grapes"');
	header('HTTP/1.0 401 Unauthorized');
	die('You are not authorized, my friend.');
}

function badAuthenticationRequest() {
	header('HTTP/1.0 400 Invalid credentials');
	die();
}

$isAuthRequest = false;
$username = "";
$password = "";
if (strpos($_SERVER["REQUEST_URI"], 'authenticate') !== false) {
	// authentication request

	$body = file_get_contents('php://input');
	$credentials = json_decode($body);
	if (is_object($credentials)) {
		if (property_exists($credentials, "username")) {
			$username = $credentials->username;
		} else {
			$username = "";
		}
		if (property_exists($credentials, "password")) {
			$password = $credentials->password;
		} else {
			$password = "";
		}
	} else {
		$username = "";
		$password = "";
	}
	
	$isAuthRequest = true;
} else {
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		if (isset($_COOKIE["grapesuser"])) {
			$authData = $_COOKIE["grapesuser"];
			$creds = base64_decode($authData);
			$creds = explode(":", $creds);
			$username = $creds[0];
			$password = $creds[1];
		}
	} else {
		$username = $_SERVER['PHP_AUTH_USER'];
		if (isset($_SERVER['PHP_AUTH_PW'])) {
			$password = $_SERVER['PHP_AUTH_PW'];
		}
	}
}
if ($username == "") {
	if ($isAuthRequest) {
		badAuthenticationRequest();
	} else {
		$username = "public";
		$password = "public";
	}
}

$repository = new MySqlRepository ( $cfgMySqlHost, $cfgMySqlUserName, $cfgMySqlPassword, $cfgMySqlDatabase );
$securityManager = new SecurityManager($repository);

$contextUser = null;
try {
	$contextUser = $securityManager->getUserByName($username);
} catch (NotFoundException $ex) {
	// username does not exist
	if ($isAuthRequest) {
		badAuthenticationRequest();
	} else {
		notAuthorized();
	}
}
if ($contextUser->password != $password) {
	if ($isAuthRequest) {
		badAuthenticationRequest();
	} else {
		notAuthorized();
	}
}

// successfull authentication
if ($isAuthRequest) {
	header('HTTP/1.0 200 credentials are valid');
	echo $contextUser->toJson();
	die();
}

?>