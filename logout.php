<?php
	require("lib/common.php");

	session_start();
	$_SESSION = array();

	colonyHTMLHeader();
?>
<p>You are now logged out. Thanks for playing!</p>
<?php
	colonyHTMLFooter();
?>
