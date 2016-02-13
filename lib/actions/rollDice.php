<?php
	if(BEFORE_ROLL !== $state)
		colonyError("Game $gameID must be in state BEFORE_ROLL to roll the dice");
	if(CHOOSE_ACTION !== $substate)
		colonyError("Game $gameID must be in substate CHOOSE_ACTION to roll the dice");

	// Roll the dice
	$total = 0;
	if(0 === $forcedRoll)
	{
		$die1 = mt_rand(1, 6);
		$die2 = mt_rand(1, 6);
		$total = $die1 + $die2;
		colonyMessage($gameID, $ID, "rolled a $total ($die1 + $die2)");
	}
	else
	{
		$statement = $db->prepare("
			UPDATE `col_games`
			SET `forcedRoll` = '0'
			WHERE `ID` = :game
		");
		$statement->bindValue("game", $gameID);
		$statement->execute();
		$statement->closeCursor();

		$total = $forcedRoll;
		colonyMessage($gameID, $ID, "was forced to roll a $total");
	}

	if(7 === $total)
	{
		$statement = $db->prepare("
			SELECT
				`playIndex`,
				(
					SELECT COUNT(*)
					FROM `col_resource_cards`
					WHERE
						`gameID` = :game AND
						`col_resource_cards`.`playerID` = `col_playing`.`playerID`
				) as `resourceCardCount`
			FROM `col_playing`
			WHERE `gameID` = :game
		");
		$statement->bindValue("game", $gameID);
		$statement->execute();

		$resourceCardCount = array();
		while(FALSE !== ($row = $statement->fetch()))
		{
			$playIndex = intval($row["playIndex"]);
			$resourceCardCount[$playIndex] = intval($row["resourceCardCount"]);
		}
		$statement->closeCursor();

		$discardingIndex = -1;
		if(7 < $resourceCardCount[$activePlayerIndex])
		{
			$discardingIndex = $activePlayerIndex;
		}
		else
		{
			for($i = ($activePlayerIndex + 1) % $playerLimit; $i != $turnPlayerIndex; $i = ($i + 1) % $playerLimit)
			{
				if(7 < $resourceCardCount[$i])
				{
					$discardingIndex = $i;
					break;
				}
			}
		}

		if(-1 === $discardingIndex)
		{
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`state` = :state,
					`substate` = :substate
				WHERE `ID` = :game
			");
			$statement->bindValue("game", $gameID);
			$statement->bindValue("state", AFTER_ROLL);
			$statement->bindValue("substate", MOVE_ROBBER);
			$statement->execute();
			$statement->closeCursor();
		}
		else
		{
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`activePlayerIndex` = :index,
					`state` = :state,
					`substate` = :substate
				WHERE `ID` = :game
			");
			$statement->bindValue("game", $gameID);
			$statement->bindValue("index", $discardingIndex);
			$statement->bindValue("state", AFTER_ROLL);
			$statement->bindValue("substate", DISCARD_RESOURCES);
			$statement->execute();
			$statement->closeCursor();

			if($activePlayerIndex != $discardingIndex)
				colonyAlertActivePlayer($gameID, $discardingIndex);
		}
	}
	else
	{
		// Build a table of all the resources to be distributed
		$statement = $db->prepare("
			SELECT
				`ID`,
				`type`
			FROM `col_tiles`
			WHERE
				`gameID` = :game AND
				`diceRoll` = :total AND
				`ID` != :robber
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("robber", $robberTileID);
		$statement->bindValue("total", $total);
		$statement->execute();

		$additions = array();
		while(FALSE !== ($row = $statement->fetch()))
		{
			$tileID = intval($row["ID"]);
			$tileType = $row["type"];
			if(!array_key_exists($tileType, $additions))
				$additions[$tileType] = array("total" => 0);

			$towns = $db->prepare("
				SELECT
					`type`,
					`playerID`
				FROM `col_towns`
				WHERE
					`gameID` = :game AND
					`playerID` != '0' AND
					(
						`tile1ID` = :tile OR
						`tile2ID` = :tile OR
						`tile3ID` = :tile
					)
			");
			$towns->bindValue("game", $gameID);
			$towns->bindValue("tile", $tileID);
			$towns->execute();

			while(FALSE !== ($row = $towns->fetch()))
			{
				$townType = intval($row["type"]);
				$playerID = intval($row["playerID"]);
				if(!array_key_exists($playerID, $additions[$tileType]))
					$additions[$tileType][$playerID] = 0;
				$additions[$tileType][$playerID] += $townType;
				$additions[$tileType]["total"] += $townType;
			}
			$towns->closeCursor();
		}
		$statement->closeCursor();

		foreach($additions as $type => $players)
		{
			// Resources can only be distributed if there are enough for everyone
			if(colonyCheckResource($gameID, 0, $type) < $players["total"])
			{
				colonyMessage($gameID, 0, "There aren't enough $type left, so no player will get any");
				continue;
			}

			foreach($players as $playerID => $resourceCount)
			{
				if("total" === $playerID)
					continue;

				colonyGetResource($gameID, $playerID, $type, $resourceCount);
				colonyMessage($gameID, $playerID, "received $resourceCount $type");
			}
		}

		$statement = $db->prepare("
			UPDATE `col_games`
			SET `state` = :state
			WHERE `ID` = :game
		");
		$statement->bindValue("state", AFTER_ROLL);
		$statement->bindValue("game", $gameID);
		$statement->execute();
		$statement->closeCursor();
	}
?>
