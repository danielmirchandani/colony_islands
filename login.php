<?php
	require("lib/common.php");

	if(!isset($_GET["code"]))
		colonyError("Must specify a code to authenticate");

	global $conf;
	$client_id = $conf["google_client_id"];
	$client_secret = $conf["google_client_secret"];
	$redirect_uri = $conf["base_url"] . "login.php";

	$client = new Google_Client();
	$client->setClientId($client_id);
	$client->setClientSecret($client_secret);
	# Even though we aren't redirecting to Google here, this needs to be set
	# because it's used to infer what kind of verification to peform
	$client->setRedirectUri($redirect_uri);
	$client->setScopes("email");
	$token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);

	session_start();
	$_SESSION["id_token"] = $token;
	header("Location: " . filter_var($_SESSION["redirect"], FILTER_SANITIZE_URL));
