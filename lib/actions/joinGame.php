<?php
	if(WAITING_FOR_PLAYERS !== $state)
		colonyError("Game $gameID is not waiting for players to join");

	if(!colonyPost("colorID"))
		colonyError("Must provide a colorID to join with");
	$colorID = intval($_POST["colorID"]);

	$statement = $db->prepare("
		SELECT '1'
		FROM `col_playing`
		WHERE
			`gameID` = :game AND
			`playerID` = :player AND
			`isJoined` = '0'
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();

	if(FALSE === $statement->fetch())
		colonyError("You must be in game $gameID and unjoined to join the game");
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT '1'
		FROM `col_playing`
		WHERE
			`gameID` = '$gameID' AND
			`colorID` = '$colorID'
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("color", $colorID);
	$statement->execute();

	if(FALSE !== $statement->fetch())
		colonyError("An unchosen color must be chosen to join the game");
	$statement->closeCursor();

	$statement = $db->prepare("
		UPDATE `col_playing`
		SET
			`colorID` = :color,
			`isJoined` = '1'
		WHERE
			`gameID` = :game AND
			`playerID` = :player
	");
	$statement->bindValue("color", $colorID);
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "has joined the game");

	$statement = $db->prepare("
		SELECT '1'
		FROM `col_playing`
		WHERE
			`gameID` = :game AND
			`isJoined` = '0'
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	$allPlayersJoined = (FALSE === $statement->fetch());
	$statement->closeCursor();

	if($allPlayersJoined)
	{
		# The game is ready to start, so shuffle the players and set the first player to go

		$statement = $db->prepare("
			SELECT `playerID`
			FROM `col_playing`
			WHERE `gameID` = :game
		");
		$statement->bindValue("game", $gameID);
		$statement->execute();

		$players = array();
		while(FALSE !== ($row = $statement->fetch()))
			$players[] = intval($row["playerID"]);
		$statement->closeCursor();

		shuffle($players);

		$i = 0;
		foreach($players as $playerID)
		{
			$statement = $db->prepare("
				UPDATE `col_playing`
				SET `playIndex` = :index
				WHERE
					`gameID` = :game AND
					`playerID` = :player
			");
			$statement->bindValue("index", $i);
			$statement->bindValue("game", $gameID);
			$statement->bindValue("player", $playerID);
			$statement->execute();
			$statement->closeCursor();

			++$i;
		}

		$statement = $db->prepare("
			UPDATE `col_games`
			SET
				`state` = :state,
				`substate` = :substate
			WHERE `ID` = :game
		");
		$statement->bindValue("state", FIRST_CHOICE);
		$statement->bindValue("substate", BUILD_SETTLEMENT);
		$statement->bindValue("game", $gameID);
		$statement->execute();
		$statement->closeCursor();
	
		colonyMessage($gameID, 0, "The game has started");
		colonyAlertActivePlayer($gameID, $activePlayerIndex);
	}
?>
