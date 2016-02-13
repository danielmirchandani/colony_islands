<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate();

	if(!colonyPost("displayName") || !colonyPost("emailAddress") || !colonyPost("password") || !colonyPost("confirmPassword"))
		colonyError("displayName, emailAddress, password, and confirmPassword must all be defined to change info");

	$password = $_POST["password"];
	if($password !== $_POST["confirmPassword"])
		colonyError("New passwords don't match. Go back to try again");

	if("" !== $password)
	{
		$statement = $db->prepare("
			UPDATE `col_players`
			SET
				`displayName` = :display,
				`emailAddress` = :email,
				`theme` = :newTheme,
				`passwordHash` = :hash
			 WHERE `ID` = :ID
		");
		$statement->bindValue("hash", colonyHashPassword($password));
	}
	else
	{
		$statement = $db->prepare("
			UPDATE `col_players`
			SET
				`displayName` = :display,
				`emailAddress` = :email,
				`theme` = :newTheme
			WHERE `ID` = :ID
		");
	}
	
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
