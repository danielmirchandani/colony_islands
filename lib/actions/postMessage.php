<?php
	if(!colonyPost("message"))
		colonyError("Must provide a message to post a message");
	$message = $_POST["message"];

	if("" === $message)
		colonyError("A non-blank message must be provided to post a message");

	$statement = $db->prepare("
		INSERT INTO `col_messages`
		SET
			`gameID` = :game,
			`playerID` = :player,
			`time` = NOW(),
			`message` = :message,
			`isSay` = '1'
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("message", $message);
	$statement->bindValue("player", $ID);
	$statement->execute();
	$statement->closeCursor();

	# Get the name of the player who posted the message
	$statement = $db->prepare("
		SELECT `col_players`.`displayName`
		FROM `col_playing`
		LEFT JOIN `col_players` ON `col_playing`.`playerID` = `col_players`.`ID`
		WHERE
			`col_playing`.`gameID` = :game AND
			`col_playing`.`playerID` = :player
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();

	$posterName = "";
	while(FALSE !== ($row = $statement->fetch()))
		$posterName = htmlspecialchars($row["displayName"]);
	$statement->closeCursor();

	# Send the message to everyone else via e-mail
	$statement = $db->prepare("
		SELECT
			`col_players`.`displayName`,
			`col_players`.`emailAddress`
		FROM `col_playing`
		LEFT JOIN `col_players` ON `col_playing`.`playerID` = `col_players`.`ID`
		WHERE
			`col_playing`.`gameID` = :game AND
			`col_playing`.`playerID` != :player
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();

	$emailMessage = htmlspecialchars($message);
	while(FALSE !== ($row = $statement->fetch()))
	{
		$displayName = htmlspecialchars($row["displayName"]);
		$emailAddress = htmlspecialchars($row["emailAddress"]);
		$text = "$posterName in <a href=\"" . colonyGameLink($gameID) . "\">game $gameID</a> said <b>$emailMessage</b>";
		colonyAlertPlayer($displayName, $emailAddress, "Game $gameID", $text);
	}
	$statement->closeCursor();
?>
