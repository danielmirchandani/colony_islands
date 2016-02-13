<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to trade with a player");
	if(DOMESTIC_TRADE !== $substate)
		colonyError("Game $gameID must be in substate DOMESTIC_TRADE to trade with a player");

	if(!colonyPost("withID"))
		colonyError("Must provide a withID to trade resources with");
	$withID = intval($_POST["withID"]);

	$statement = $db->prepare("
		SELECT `playIndex`
		FROM `col_playing`
		WHERE
			`gameID` = :game AND
			`playerID` = :tradee AND
			`playerID` != :trader
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("tradee", $withID);
	$statement->bindValue("trader", $ID);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("Must provide a non-self withID in game $gameID to trade resources with");

	$withPlayerIndex = intval($row["playIndex"]);
	$statement->closeCursor();

	$awayTypes = array();
	$forTypes = array();
	$resourceTypes = array("Brick", "Grain", "Lumber", "Ore", "Wool");
	$allAway = TRUE;
	$allFor = TRUE;
	$allZero = TRUE;
	foreach($resourceTypes as $resourceType)
	{
		$awayString = "away$resourceType";
		if(!colonyPost($awayString))
			colonyError("Must provide an amount of $awayString to trade resources away");
		$awayTypes[$resourceType] = intval($_POST[$awayString]);
		if($awayTypes[$resourceType] < 0)
			colonyError("Must specify at least 0 $resourceType to trade resources away");
		if(colonyCheckResource($gameID, $ID, $resourceType) < $awayTypes[$resourceType])
			colonyError("Must specify at most $resourceType you control to trade resources away");

		$forString = "for$resourceType";
		if(!colonyPost($forString))
			colonyError("Must provide an amount of $forString to trade resources for");
		$forTypes[$resourceType] = intval($_POST[$forString]);
		if($forTypes[$resourceType] < 0)
			colonyError("Must specify at least 0 $resourceType to trade resources for");

		if((0 < $awayTypes[$resourceType]) && (0 < $forTypes[$resourceType]))
			colonyError("Must not trade $resourceType for itself");

		$tradeAmount = $forTypes[$resourceType] - $awayTypes[$resourceType];
		if($tradeAmount < 0)
		{
			$allFor = FALSE;
			$allZero = FALSE;
		}
		else if(0 < $tradeAmount)
		{
			$allAway = FALSE;
			$allZero = FALSE;
		}
	}

	if($allAway)
		colonyError("Must specify at least one resource to trade resources for");
	if($allFor)
		colonyError("Must specify at least one resource to trade resources away");
	if($allZero)
		colonyError("Must specify at least one resource to trade resources");

	$statement = $db->prepare("
		INSERT INTO `col_trades`
		SET
			`gameID` = :game,
			`player1ID` = :trader,
			`player2ID` = :tradee
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("tradee", $withID);
	$statement->bindValue("trader", $ID);
	$statement->execute();
	$statement->closeCursor();

	$tradeID = intval($db->lastInsertID());

	foreach($resourceTypes as $resourceType)
	{
		$tradeAmount = $forTypes[$resourceType] - $awayTypes[$resourceType];
		if(0 !== $tradeAmount)
		{
			$statement = $db->prepare("
				INSERT INTO `col_trade_cards`
				SET
					`tradeID` = :trade,
					`type` = :resource,
					`amount` = :amount
			");
			$statement->bindValue("amount", $tradeAmount);
			$statement->bindValue("resource", $resourceType);
			$statement->bindValue("trade", $tradeID);
			$statement->execute();
			$statement->closeCursor();
		}
	}

	$statement = $db->prepare("
		UPDATE `col_games`
		SET
			`activePlayerIndex` = :active,
			`substate` = :substate
		WHERE `ID` = :game
	");
	$statement->bindValue("active", $withPlayerIndex);
	$statement->bindValue("game", $gameID);
	$statement->bindValue("substate", REVIEW_TRADE);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "is proposing a trade");
	colonyAlertActivePlayer($gameID, $withPlayerIndex);
?>
