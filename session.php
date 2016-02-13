<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticateCookie();

	colonyHTMLStart();
?>
<p>Logged in as <?php echo($loggedIn["displayName"]);?> (<?php echo($loggedIn["emailAddress"]);?>)</p>
<p><a href="logout.php">Log out</a></p>
<?php
	colonyHTMLEnd();
?>
