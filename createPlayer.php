<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate(TRUE);

	if(!colonyPost("displayName") || !colonyPost("emailAddress"))
		colonyError("displayName and emailAddress must all be defined to create a player");
	$displayName = $_POST["displayName"];
	$emailAddress = $_POST["emailAddress"];

	$statement = $db->prepare("
		SELECT '1'
		FROM `col_players`
		WHERE `emailAddress` = :email
	");
	$statement->bindValue("email", $emailAddress);
	$statement->execute();

	if(FALSE !== $statement->fetch())
		colonyError("An unregistered e-mail address must be provided to create a player");
	$statement->closeCursor();

	$newPassword = colonyGeneratePassword();

	$statement = $db->prepare("
		INSERT INTO `col_players`
		SET
			`displayName` = :name,
			`emailAddress` = :email,
			`passwordHash` = :hash
	");
	$statement->bindValue("name", $displayName);
	$statement->bindValue("email", $emailAddress);
	$statement->bindValue("hash", colonyHashPassword($newPassword));
	$statement->execute();

	if(1 !== $statement->rowCount())
		colonyError("Error creating player");
	$statement->closeCursor();

	colonyAlertPlayer($displayName, $emailAddress, "Welcome", "Welcome to Colony Islands. Your username is this e-mail address and your password is \"$newPassword\".");
	header("Location: index.php");
?>
