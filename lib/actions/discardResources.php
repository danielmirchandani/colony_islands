<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to discard resources");
	if(DISCARD_RESOURCES !== $substate)
		colonyError("Game $gameID must be in substate DISCARD_RESOURCES to discard resources");

	$discardTotal = 0;
	$discardTypes = array("Brick" => 0, "Grain" => 0, "Lumber" => 0, "Ore" => 0, "Wool" => 0);
	foreach($discardTypes as $resourceType => $resourceCount)
	{
		if(!colonyPost($resourceType))
			colonyError("Must provide an amount of $resourceType to discard resources");
		$amount = intval($_POST[$resourceType]);
		if($amount < 0)
			colonyError("Must specify at least 0 $resourceType to discard resources");
		if(colonyCheckResource($gameID, $ID, $resourceType) < $amount)
			colonyError("Must specify at most $resourceType you control to discard resources");
		$discardTypes[$resourceType] = $amount;
		$discardTotal += $discardTypes[$resourceType];
	}

	$statement = $db->prepare("
		SELECT COUNT(*) as `count` 
		FROM `col_resource_cards`
		WHERE
			`gameID` = :game AND
			`playerID` = :player
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();

	$row = $statement->fetch();
	$resourceCount = intval($row["count"]);
	$statement->closeCursor();

	$discardAmount = intval(floor($resourceCount / 2));
	if($discardTotal !== $discardAmount)
		colonyError("Exactly $discardAmount resource cards must be selected to discard resources");

	foreach($discardTypes as $resourceType => $resourceCount)
	{
		if(0 === $resourceCount)
			continue;
		colonyUseResource($gameID, $ID, $resourceType, $resourceCount);
		colonyMessage($gameID, $ID, "has discarded $resourceCount $resourceType");
	}

	$statement = $db->prepare("
		SELECT
			`playIndex`,
			(
				SELECT COUNT(*) as `count`
				FROM `col_resource_cards`
				WHERE
					`gameID` = :game AND
					col_resource_cards.`playerID` = col_playing.`playerID`
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
	for($i = ($activePlayerIndex + 1) % $playerLimit; $i != $turnPlayerIndex; $i = ($i + 1) % $playerLimit)
	{
		if(7 < $resourceCardCount[$i])
		{
			$discardingIndex = $i;
			break;
		}
	}

	if(-1 === $discardingIndex)
	{
		$statement = $db->prepare("
			UPDATE `col_games`
			SET
				`activePlayerIndex` = :index,
				`substate` = :substate
			WHERE `ID` = :game
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("index", $turnPlayerIndex);
		$statement->bindValue("substate", MOVE_ROBBER);
		$statement->execute();
		$statement->closeCursor();

		if($activePlayerIndex != $turnPlayerIndex)
			colonyAlertActivePlayer($gameID, $turnPlayerIndex);
	}
	else
	{
		$statement = $db->prepare("
			UPDATE `col_games`
			SET `activePlayerIndex` = :index
			WHERE `ID` = :game
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("index", $discardingIndex);
		$statement->execute();
		$statement->closeCursor();

		if($activePlayerIndex != $discardingIndex)
			colonyAlertActivePlayer($gameID, $discardingIndex);
	}
?>
