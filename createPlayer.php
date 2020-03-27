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

	$statement = $db->prepare("
		INSERT INTO `col_players`
		SET
			`displayName` = :name,
			`emailAddress` = :email,
			`passwordHash` = ''
	");
	$statement->bindValue("name", $displayName);
	$statement->bindValue("email", $emailAddress);
	$statement->execute();

	if(1 !== $statement->rowCount())
		colonyError("Error creating player");
	$statement->closeCursor();

	colonyAlertPlayer($displayName, $emailAddress, "Welcome", "Welcome to Colony Islands. Visiting the site for the first time will ask which Google account you want to use while playing. You must choose the account matching this email address, otherwise you might get stuck in a login loop. Contact Dan or whomever got you an account for assistance.");
	header("Location: index.php");
?>
