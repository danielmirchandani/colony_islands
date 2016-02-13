<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate(TRUE);

	if(!colonyPost("playerID"))
		colonyError("Must provide playerID for password reset");
	$playerID = intval($_POST["playerID"]);

	$statement = $db->prepare("
		SELECT
			`displayName`,
			`emailAddress`
		FROM `col_players`
		WHERE `ID` = :ID
	");
	$statement->bindValue("ID", $playerID);
	$statement->execute();

	$row = $statement->fetch();
	$displayName = htmlspecialchars($row["displayName"]);
	$emailAddress = htmlspecialchars($row["emailAddress"]);
	$statement->closeCursor();

	$newPassword = colonyGeneratePassword();

	$statement = $db->prepare("
		UPDATE `col_players`
		SET `passwordHash` = :hash
		WHERE `ID` = :player
	");
	$statement->bindValue("hash", colonyHashPassword($newPassword));
	$statement->bindValue("player", $playerID);
	$statement->execute();

	if(1 !== $statement->rowCount())
		colonyError("Error resetting password");
	$statement->closeCursor();

	colonyAlertPlayer($displayName, $emailAddress, "Password Reset", "Your password has been reset to \"$newPassword\".");
	header("Location: index.php");
?>
