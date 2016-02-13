<?php
	require("lib/common.php");

	# Use a largish negative number to delete the cookie
	colonyCookieSession("", -1000);

	colonyHTMLStart();
?>
<p>You are now logged out. Thanks for playing!</p>
<?php
	colonyHTMLEnd();
?>
