<?php
	require("lib/common.php");

	session_start();
	unset($_SESSION['access_token']);
	// Create a new session id to prevent session fixation.
	session_regenerate_id();

	colonyHTMLStart();
?>
<p>You are now logged out. Thanks for playing!</p>
<?php
	colonyHTMLEnd();
?>
