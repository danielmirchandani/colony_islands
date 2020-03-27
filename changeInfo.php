<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate();

	if(!colonyPost("displayName") || !colonyPost("emailAddress") || !colonyPost("theme"))
		colonyError("displayName, emailAddress, and theme must all be defined to change info");

	$statement = $db->prepare("
		UPDATE `col_players`
		SET
			`displayName` = :display,
			`emailAddress` = :email,
			`theme` = :newTheme
		WHERE `ID` = :ID
	");
	$statement->bindValue("display", $_POST["displayName"]);
	$statement->bindValue("email", $_POST["emailAddress"]);
	$statement->bindValue("newTheme", $_POST["theme"]);
	$statement->bindValue("ID", $loggedIn["ID"]);
	$statement->execute();
	$statement->closeCursor();

	if(1 !== $statement->rowCount())
		colonyError("Error updating information; contact Dan for assistance");

	header("Location: index.php");
?>
