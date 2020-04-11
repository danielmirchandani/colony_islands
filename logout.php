<?php
	require("lib/common.php");

	session_start();
	$_SESSION = array();

	colonyHTMLStart();
?>
<p>You are now logged out. Thanks for playing!</p>
<?php
	colonyHTMLEnd();
?>
