<?php
	require("lib/common.php");

	if(!colonyPost("emailAddress"))
		colonyError("Must specify 'emailAddress'");
	$emailAddress = $_POST["emailAddress"];

	if(!colonyPost("password"))
		colonyError("Must specify 'password'");
	$password = $_POST["password"];

	list($db, $loggedIn) = colonyAuthenticateEmailPassword($emailAddress, $password);
	if(NULL === $loggedIn)
		colonyError("Authentication failed; go back and try again");

	# 30 days in seconds
	$expireSeconds = 60 * 60 * 24 * 30;
	colonyCookieSession("value", $expireSeconds);

	colonyHTMLStart();
?>
<p>You are now logged in as <?php echo($loggedIn["displayName"]);?> (<?php echo($loggedIn["emailAddress"]);?>). Go back and refresh the page to continue.</p>
<?php
	colonyHTMLEnd();
?>
