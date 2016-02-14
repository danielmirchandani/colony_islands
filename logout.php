<?php
	require("lib/common.php");

	session_start();
	unset($_SESSION['access_token']);

	colonyHTMLStart();
?>
<p>You are now logged out. Thanks for playing!</p>
<?php
	colonyHTMLEnd();
?>
