<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to use a development card");
	if(USE_DEVELOPMENT_CARD !== $substate)
		colonyError("Game $gameID must be in substate USE_DEVELOPMENT_CARD to use a development card");

	if(!colonyPost("developmentCardID"))
		colonyError("Must provide a developmentCardID to use a development card");
	$developmentCardID = $_POST["developmentCardID"];

	$statement = $db->prepare("
		SELECT '1'
		FROM `col_development_cards`
		WHERE
			`gameID` = :game AND
			`playerID` = :player AND
			`turnUsed` = :turn
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("turn", $currentTurn);
	$statement->execute();

	if(FALSE !== $statement->fetch())
		colonyError("Only one development card per turn can be selected to use a development card");
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT `type`
		FROM `col_development_cards`
		WHERE
			`ID` = :card AND
			`gameID` = :game AND
			`playerID` = :player AND
			`turnBought` != :turn AND
			`turnUsed` = '0'
	");
	$statement->bindValue("card", $developmentCardID);
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("turn", $currentTurn);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("A controlled, unused development card bought on a previous turn must be selected to use a development card");

	$type = $row["type"];
	$statement->closeCursor();

	if("Road Building" === $type)
	{
		// If the player already has the maximum number of roads, just move to CHOOSE_ACTION
		$statement = $db->prepare("
			SELECT COUNT(*) as `roadCount`
			FROM `col_roads`
			WHERE
				`gameID` = :game AND
				`playerID` = :player
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $ID);
		$statement->execute();

		$row = $statement->fetch();
		$roadCount = intval($row["roadCount"]);
		$statement->closeCursor();

		if($roadCount === $roadLimit)
			colonySetSubstate($gameID, CHOOSE_ACTION);
		else
			colonySetSubstate($gameID, ROAD_BUILDING_1);
	}
	else if("Soldier" === $type)
		colonySetSubstate($gameID, MOVE_ROBBER);
	else if("Victory Point" === $type)
		colonySetSubstate($gameID, CHOOSE_ACTION);
	else if("Year of Plenty" === $type)
		colonySetSubstate($gameID, CELEBRATE_YEAR_OF_PLENTY);
	else if("Monopoly" === $type)
		colonySetSubstate($gameID, MONOPOLIZE);
	else
		colonyError("$type is not a valid development card type to use a development card");

	$statement = $db->prepare("
		UPDATE `col_development_cards`
		SET `turnUsed` = :turn
		WHERE `ID` = :card
	");
	$statement->bindValue("card", $developmentCardID);
	$statement->bindValue("turn", $currentTurn);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "has used a $type development card");

	// Check to see if this changes who has the largest army
	if("Soldier" === $type)
	{
		$statement = $db->prepare("
			SELECT COUNT(*) as `armyAmount`
			FROM `col_development_cards`
			WHERE
				`gameID` = :game AND
				`type` = :soldier AND
				`playerID` = :player AND
				`turnUsed` != '0'
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $ID);
		$statement->bindValue("soldier", "Soldier");
		$statement->execute();

		$row = $statement->fetch();
		$armyAmount = intval($row["armyAmount"]);
		$statement->closeCursor();

		if((3 <= $armyAmount) && ($largestArmyAmount < $armyAmount))
		{
			# Even if the same player develops the largest army, update the
			# size so it stays current
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`largestArmyAmount` = :army,
					`largestArmyID` = :player
				WHERE `ID` = :game
			");
			$statement->bindValue("army", $armyAmount);
			$statement->bindValue("game", $gameID);
			$statement->bindValue("player", $ID);
			$statement->execute();
			$statement->closeCursor();

			if($largestArmyID !== $ID)
			{
				# Recalculate the number of points the previous owner has
				if(0 !== $largestArmyID)
				{
					colonyMessage($gameID, $largestArmyID, "has lost the largest army");
					colonyCheckWin($gameID, $largestArmyID);
				}

				colonyMessage($gameID, $ID, "has developed the largest army");
				colonyCheckWin($gameID, $ID);
			}
		}
	}
	# Using a victory point can't win the game since simply owning it is
	# enough to win, but update the number of visible points
	else if("Victory Point" === $type)
	{
		colonyCheckWin($gameID, $ID);
	}
?>
