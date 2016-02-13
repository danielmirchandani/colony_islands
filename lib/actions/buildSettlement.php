<?php
	if((FIRST_CHOICE !== $state) && (SECOND_CHOICE !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state FIRST_CHOICE, SECOND_CHOICE, or AFTER_ROLL to build a settlement");
	if(BUILD_SETTLEMENT !== $substate)
		colonyError("Game $gameID must be in substate BUILD_SETTLEMENT to build a settlement");

	if(!colonyPost("townID"))
		colonyError("Must provide a townID to build a settlement");
	$townID = intval($_POST["townID"]);

	# Find the three tiles this settlement is surrounded by
	$statement = $db->prepare("
		SELECT
			`tile1ID`,
			`tile2ID`,
			`tile3ID`
		FROM `col_towns`
		WHERE
			`ID` = :town AND
			`type` = '0' AND
			`playerID` = '0'
	");
	$statement->bindValue("town", $townID);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("A controlled town-point without any town must be selected to build a settlement");

	$tile1ID = intval($row["tile1ID"]);
	$tile2ID = intval($row["tile2ID"]);
	$tile3ID = intval($row["tile3ID"]);
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT '1'
		FROM col_roads
		JOIN col_towns ON col_roads.town1ID = col_towns.ID
		WHERE
			col_roads.town2ID = :townID AND
			col_towns.playerID != 0
		UNION
		SELECT col_roads.town2ID
		FROM col_roads
		JOIN col_towns ON col_roads.town2ID = col_towns.ID
		WHERE
			col_roads.town1ID = :townID AND
			col_towns.playerID != 0
	");
	$statement->bindValue("townID", $townID);
	$statement->execute();

	if(FALSE !== $statement->fetch())
		colonyError("A town-point must be selected which isn't next to another settlement to build a settlement");
	$statement->closeCursor();

	if(AFTER_ROLL === $state)
	{
		$statement = $db->prepare("
			SELECT '1'
			FROM `col_roads`
			WHERE
				`playerID` = '$ID' AND
				(
					`town1ID` = '$townID' OR
					`town2ID` = '$townID'
				)
		");
		$statement->bindValue("town", $townID);
		$statement->execute();

		if(FALSE === $statement->fetch())
			colonyError("A town-point connected to a controlled road must be selected during AFTER_ROLL to build a settlement");
		$statement->closeCursor();
	}

	$statement = $db->prepare("
		SELECT COUNT(*) as `settlementCount`
		FROM `col_towns`
		WHERE
			`gameID` = :game AND
			`type` = '1' AND
			`playerID` = :player
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();

	$row = $statement->fetch();
	$settlementCount = intval($row["settlementCount"]);
	$statement->closeCursor();

	if($settlementLimit === $settlementCount)
		colonyError("Players must have less settlements than the limit of $settlementLimit to build a settlement");

	// Do you have enough resources?
	if(AFTER_ROLL === $state)
	{
		if(colonyCheckResource($gameID, $ID, "Brick") < 1)
			colonyError("One Brick is required to build a settlement");
		if(colonyCheckResource($gameID, $ID, "Grain") < 1)
			colonyError("One Grain is required to build a settlement");
		if(colonyCheckResource($gameID, $ID, "Lumber") < 1)
			colonyError("One Lumber is required to build a settlement");
		if(colonyCheckResource($gameID, $ID, "Wool") < 1)
			colonyError("One Wool is required to build a settlement");

		colonyUseResource($gameID, $ID, "Brick", 1);
		colonyUseResource($gameID, $ID, "Grain", 1);
		colonyUseResource($gameID, $ID, "Lumber", 1);
		colonyUseResource($gameID, $ID, "Wool", 1);
	}

	$statement = $db->prepare("
		UPDATE `col_towns`
		SET
			`playerID` = :player,
			`type` = '1'
		WHERE
			`ID` = :town
	");
	$statement->bindValue("player", $ID);
	$statement->bindValue("town", $townID);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "built a settlement at point $townID");

	if(!colonyCheckWin($gameID, $ID))
	{
		if(FIRST_CHOICE === $state)
		{
			colonySetSubstate($gameID, BUILD_ROAD);
		}
		else if(SECOND_CHOICE === $state)
		{
			// Distribute starting resources
			$statement = $db->prepare("
				SELECT `type`
				FROM `col_tiles`
				WHERE
					`ID` = :tile1 OR
					`ID` = :tile2 OR
					`ID` = :tile3
			");
			$statement->bindValue("tile1", $tile1ID);
			$statement->bindValue("tile2", $tile2ID);
			$statement->bindValue("tile3", $tile3ID);
			$statement->execute();

			while(FALSE !== ($row = $statement->fetch()))
			{
				$type = $row["type"];
				if(("Desert" !== $type) && ("Water" !== $type))
				{
					colonyGetResource($gameID, $ID, $type, 1);
					colonyMessage($gameID, $ID, "received a $type");
				}
			}
			$statement->closeCursor();

			colonySetSubstate($gameID, BUILD_ROAD);
		}
		else if(AFTER_ROLL === $state)
		{
			# Check if the longest road was broken (a player can't break their
			# own longest road, so don't check if this player already has it)
			$endGame = FALSE;
			if($ID !== $longestRoadID)
			{
				$length = colonyCheckLongestRoad($gameID, $longestRoadID);
				# If the longest road was broken, find out who has the longest
				# road now
				if($length < $longestRoadAmount)
				{
					$statement = $db->prepare("
						SELECT `playerID`
						FROM `col_playing`
						WHERE
							`gameID` = :game AND
							`playerID` != :player
					");
					$statement->bindValue("game", $gameID);
					$statement->bindValue("player", $longestRoadID);
					$statement->execute();

					# Start the roads array with the player already calculated
					$roads = array($length => array($longestRoadID));
					while(FALSE !== ($row = $statement->fetch()))
					{
						$playerID = intval($row["playerID"]);
						$playerLength = colonyCheckLongestRoad($gameID, $playerID);
						if(!array_key_exists($playerLength, $roads))
							$roads[$playerLength] = array();
						array_push($roads[$playerLength], $playerID);
					}
					$statement->closeCursor();

					# Reverse-sort the array by keys to find the longest road,
					# but scan backwards in case of a tie
					krsort($roads);
					$bestLength = 0;
					$bestID = 0;
					foreach($roads as $length => $players)
					{
						# If more than one player is tied for the longest,
						# don't consider this length and step back
						if(1 < count($players))
							continue;

						# The longest road must be at least 5 long and if this
						# road is less than 5 long, so are the rest
						if($length < 5)
							break;

						# The longest road belongs to the only person at this
						# length
						$bestLength = $length;
						$bestID = $players[0];
						break;
					}

					# Even if the same player builds a longest road, update the length
					# so it stays current
					$statement = $db->prepare("
						UPDATE `col_games`
						SET
							`longestRoadAmount` = :amount,
							`longestRoadID` = :player
						WHERE `ID` = :game
					");
					$statement->bindValue("amount", $bestLength);
					$statement->bindValue("player", $bestID);
					$statement->bindValue("game", $gameID);
					$statement->execute();
					$statement->closeCursor();

					# Recalculate the number of points the previous and new
					# owners have if they aren't the same person
					if($longestRoadID !== $bestID)
					{
						if(0 !== $longestRoadID)
						{
							colonyCheckWin($gameID, $longestRoadID);
							colonyMessage($gameID, $longestRoadID, "lost the longest road");
						}
						if(0 !== $bestID)
						{
							$endGame = colonyCheckWin($gameID, $bestID);
							colonyMessage($gameID, $bestID, "built the longest road");
						}
					}
				}
			}

			if(!$endGame)
				colonySetSubstate($gameID, CHOOSE_ACTION);
		}
	}
?>
