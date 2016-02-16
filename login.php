<?php
	require("lib/common.php");

	if(!isset($_GET["code"]))
		colonyError("Must specify a code to authenticate");

	global $conf;
	$client = new Google_Client();
	$client->setClientId($conf["google_client_id"]);
	$client->setClientSecret($conf["google_client_secret"]);
	$client->setScopes("email");

	$token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);
	$_SESSION["id_token"] = $token;

	colonyAuthenticateGoogleIdentity();

	session_start();
	header("Location: " . filter_var($_SESSION["redirect"], FILTER_SANITIZE_URL));