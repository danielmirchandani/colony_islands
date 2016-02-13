<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to steal a resource card");
	if(STEAL_RESOURCE !== $substate)
		colonyError("Game $gameID must be in substate STEAL_RESOURCE to steal a resource card");

	if(!colonyPost("playerID"))
		colonyError("Must provide a playerID to steal a resource card");
	$playerID = intval($_POST["playerID"]);

	if(0 === $playerID)
		colonyError("Must select an existing player to steal a resource card");

	$statement = $db->prepare("
		SELECT '1'
		FROM `col_towns`
		WHERE
			`gameID` = :game AND
			`playerID` = :opponent AND
			`playerID` != :player AND
			(
				`tile1ID` = :robber OR
				`tile2ID` = :robber OR
				`tile3ID` = :robber
			)
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("opponent", $playerID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("robber", $robberTileID);
	$statement->execute();

	if(FALSE === $statement->fetch())
		colonyError("An opponent owning a town on the robber tile must be selected to steal a resource card");
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT
			`ID`,
			`type`
		FROM `col_resource_cards`
		WHERE
			`gameID` = :game AND
			`playerID` = :opponent
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("opponent", $playerID);
	$statement->execute();

	$resources = array();
	while(FALSE !== ($row = $statement->fetch()))
	{
		$cardID = intval($row["ID"]);
		$resources[$cardID] = $row["type"];
	}
	$statement->closeCursor();

	if(0 < count($resources))
	{
		$cardID = array_rand($resources);
		$cardType = $resources[$cardID];
		
		$statement = $db->prepare("
			UPDATE `col_resource_cards`
			SET `playerID` = :player
			WHERE `ID` = :card
		");
		$statement->bindValue("card", $cardID);
		$statement->bindValue("player", $ID);
		$statement->execute();
		$statement->closeCursor();

		colonyMessage($gameID, $ID, "stole a resource card");
		colonyMessage($gameID, $playerID, "lost a resource card");

		$statement = $db->prepare("
			SELECT
				`displayName`,
				`emailAddress`
			FROM `col_players`
			WHERE
				`ID` = :player
		");
		$statement->bindValue("player", $playerID);
		$statement->execute();

		$row = $statement->fetch();
		$displayName = htmlspecialchars($row["displayName"]);
		$emailAddress = htmlspecialchars($row["emailAddress"]);
		$statement->closeCursor();

		$message = "A $cardType was stolen from you in <a href=\"" . colonyGameLink($gameID) . "\">game $gameID</a>.";
		colonyAlertPlayer($displayName, $emailAddress, "Game $gameID", $message);
	}

	colonySetSubstate($gameID, CHOOSE_ACTION);
?>
