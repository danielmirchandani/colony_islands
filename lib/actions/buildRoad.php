<?php
	if((FIRST_CHOICE === $state) || (SECOND_CHOICE === $state))
	{
		if(BUILD_ROAD !== $substate)
			colonyError("Game $gameID must be in substate BUILD_ROAD during FIRST_CHOICE or SECOND_CHOICE to build a road");
	}
	else if(BEFORE_ROLL === $state)
	{
		if((ROAD_BUILDING_1 !== $substate) && (ROAD_BUILDING_2 !== $substate))
			colonyError("Game $gameID must be in substate ROAD_BUILDING_1 or ROAD_BUILDING_2 during BEFORE_ROLL to build a road");
	}
	else if(AFTER_ROLL === $state)
	{
		if((BUILD_ROAD !== $substate) && (ROAD_BUILDING_1 !== $substate) && (ROAD_BUILDING_2 !== $substate))
			colonyError("Game $gameID must be in substate BUILD_ROAD, ROAD_BUILDING_1, or ROAD_BUILDING_2 during AFTER_ROLL to build a road");
	}
	else
		colonyError("Game $gameID must be in state FIRST_CHOICE, SECOND_CHOICE, BEFORE_ROLL, or AFTER_ROLL to build a road");

	if(!colonyPost("roadID"))
		colonyError("Must provide a roadID to build a road");
	$roadID = intval($_POST["roadID"]);

	$statement = $db->prepare("
		SELECT
			`town1ID`,
			`town2ID`
		FROM `col_roads`
		WHERE
			`ID` = :road AND
			`playerID` = '0'
	");
	$statement->bindValue("road", $roadID);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("An un-owned road-line must be selected to build a road");

	$townIDs = array();
	$townIDs[1] = intval($row["town1ID"]);
	$townIDs[2] = intval($row["town2ID"]);
	$statement->closeCursor();

	$roadCount = -1;
	if((FIRST_CHOICE === $state) || (SECOND_CHOICE === $state))
	{
		$statement = $db->prepare("
			SELECT `ID`
			FROM `col_towns`
			WHERE
				`playerID` = '$ID' AND
				(
					`ID` = :town1 OR
					`ID` = :town2
				)
		");
		$statement->bindValue("town1", $townIDs[1]);
		$statement->bindValue("town2", $townIDs[2]);
		$statement->execute();

		$row = $statement->fetch();
		if(FALSE === $row)
			colonyError("A road-line connected to an controlled town must be selected to build a road");

		$townID = intval($row["ID"]);
		$statement->closeCursor();

		$statement = $db->prepare("
			SELECT '1'
			FROM `col_roads`
			WHERE
				`playerID` != '0' AND
				(
					`town1ID` = :town OR
					`town2ID` = :town
				)
		");
		$statement->bindValue("town", $townID);
		$statement->execute();

		if(FALSE !== $statement->fetch())
			colonyError("A road-line connected to a controlled town without any other connected roads must be selected to build a road");
		$statement->closeCursor();
	}
	else if((BEFORE_ROLL === $state) || (AFTER_ROLL === $state))
	{
		$connecting = FALSE;

		# For each town on the road ...
		for($i = 1; $i <= 2; ++$i)
		{
			# Find a road connecting to the desired road
			$statement = $db->prepare("
				SELECT '1'
				FROM `col_roads`
				WHERE
					`playerID` = '$ID' AND
					(
						`town1ID` = :town OR
						`town2ID` = :town
					)
			");
			$statement->bindValue("town", $townIDs[$i]);
			$statement->execute();

			if(FALSE === $statement->fetch())
				continue;
			$statement->closeCursor();

			# Check if the desired road went through someone else's town
			$statement = $db->prepare("
				SELECT '1'
				FROM `col_towns`
				WHERE
					`playerID` != 0 AND
					`playerID` != '$ID' AND
					`ID` = :town
			");
			$statement->bindValue("town", $townIDs[$i]);
			$statement->execute();

			if(FALSE !== $statement->fetch())
				colonyError("A road-line not running through an opponent's town must be selected to build a road");
			$statement->closeCursor();

			$connecting = TRUE;
		}

		if(!$connecting)
			colonyError("A road-line connected to a controlled road must be selected to build a road");

		# Does the player have too many roads?
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

		if($roadLimit === $roadCount)
			colonyError("Players must have less roads than the limit of $roadLimit to build a road");
	}

	# Does the player have enough resources?
	if((AFTER_ROLL === $state) && (BUILD_ROAD === $substate))
	{
		if(colonyCheckResource($gameID, $ID, "Brick") < 1)
			colonyError("One Brick is required to build a road");
		if(colonyCheckResource($gameID, $ID, "Lumber") < 1)
			colonyError("One Lumber is required to build a road");

		colonyUseResource($gameID, $ID, "Brick", 1);
		colonyUseResource($gameID, $ID, "Lumber", 1);
	}

	$statement = $db->prepare("
		UPDATE `col_roads`
		SET `playerID` = '$ID'
		WHERE
			`ID` = :road
	");
	$statement->bindValue("road", $roadID);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "built a road at line $roadID");

	if(FIRST_CHOICE === $state)
	{
		++$activePlayerIndex;
		if($activePlayerIndex < $playerLimit)
		{
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`activePlayerIndex` = :activePlayer,
					`substate` = :substate
				WHERE `ID` = :game
			");
			$statement->bindValue("activePlayer", $activePlayerIndex);
			$statement->bindValue("substate", BUILD_SETTLEMENT);
			$statement->bindValue("game", $gameID);
			$statement->execute();
			$statement->closeCursor();

			colonyAlertActivePlayer($gameID, $activePlayerIndex);
		}
		else
		{
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`state` = :state,
					`substate` = :substate
				WHERE `ID` = :game
			");
			$statement->bindValue("state", SECOND_CHOICE);
			$statement->bindValue("substate", BUILD_SETTLEMENT);
			$statement->bindValue("game", $gameID);
			$statement->execute();
			$statement->closeCursor();
		}
	}
	else if(SECOND_CHOICE === $state)
	{
		--$activePlayerIndex;
		if(0 <= $activePlayerIndex)
		{
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`activePlayerIndex` = :activePlayer,
					`substate` = :substate
				WHERE `ID` = :game
			");
			$statement->bindValue("activePlayer", $activePlayerIndex);
			$statement->bindValue("substate", BUILD_SETTLEMENT);
			$statement->bindValue("game", $gameID);
			$statement->execute();
			$statement->closeCursor();

			colonyAlertActivePlayer($gameID, $activePlayerIndex);
		}
		else
		{
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`currentTurn` = '1',
					`state` = :state,
					`substate` = :substate
				WHERE `ID` = :game
			");
			$statement->bindValue("state", BEFORE_ROLL);
			$statement->bindValue("substate", CHOOSE_ACTION);
			$statement->bindValue("game", $gameID);
			$statement->execute();
			$statement->closeCursor();
		}
	}
	else if((BEFORE_ROLL === $state) || (AFTER_ROLL === $state))
	{
		$endGame = FALSE;
		$roadLength = colonyCheckLongestRoad($gameID, $ID);
		if((5 <= $roadLength) && ($longestRoadAmount < $roadLength))
		{
			# Even if the same player builds a longest road, update the length
			# so it stays current
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`longestRoadAmount` = :amount,
					`longestRoadID` = :player
				WHERE `ID` = :game
			");
			$statement->bindValue("amount", $roadLength);
			$statement->bindValue("player", $ID);
			$statement->bindValue("game", $gameID);
			$statement->execute();
			$statement->closeCursor();

			if($longestRoadID !== $ID)
			{
				# Recalculate the number of points the previous owner has
				if(0 !== $longestRoadID)
				{
					colonyCheckWin($gameID, $longestRoadID);
					colonyMessage($gameID, $longestRoadID, "has lost the longest road");
				}

				colonyMessage($gameID, $ID, "has built the longest road");
				$endGame = colonyCheckWin($gameID, $ID);
			}
		}

		if(!$endGame)
		{
			if((ROAD_BUILDING_1 === $substate) && ($roadCount + 1 < $roadLimit))
				colonySetSubstate($gameID, ROAD_BUILDING_2);
			else
				colonySetSubstate($gameID, CHOOSE_ACTION);
		}
	}
?>
