<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to accept a trade");
	if(REVIEW_TRADE !== $substate)
		colonyError("Game $gameID must be in substate REVIEW_TRADE to accept a trade");

	# Verify the trade to be accepted
	$statement = $db->prepare("
		SELECT
			`ID`,
			`player1ID`
		FROM `col_trades`
		WHERE
			`gameID` = :game AND
			`player2ID` = :player2 AND
			`status` = :status
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player2", $ID);
	$statement->bindValue("status", TRADE_REVIEW);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("You don't have any trades to accept");

	$tradeID = intval($row["ID"]);
	$withID = intval($row["player1ID"]);
	$statement->closeCursor();

	# Verify this player has the resources to give away
	$statement = $db->prepare("
		SELECT
			`amount`,
			`type`
		FROM `col_trade_cards`
		WHERE `tradeID` = :trade
		ORDER BY `amount`
	");
	$statement->bindValue("trade", $tradeID);
	$statement->execute();

	$deltaGiven = 0;
	$give = array();
	$take = array();
	while(FALSE !== ($row = $statement->fetch()))
	{
		$amount = intval($row["amount"]);
		$type = $row["type"];

		if(0 < $amount)
		{
			if(colonyCheckResource($gameID, $ID, $type) < $amount)
				colonyError("You don't have enough resources to accept a trade");

			$give[$type] = $amount;
		}
		else if($amount < 0)
		{
			$take[$type] = -$amount;
		}

		$deltaGiven += $amount;
	}
	$statement->closeCursor();

	# Give resources
	foreach($give as $type => $amount)
	{
		$statement = $db->prepare("
			UPDATE `col_resource_cards`
			SET `playerID` = :with
			WHERE
				`gameID` = :game AND
				`type` = :type AND
				`playerID` = :player
			LIMIT $amount
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $ID);
		$statement->bindValue("type", $type);
		$statement->bindValue("with", $withID);
		$statement->execute();
		$statement->closeCursor();
	}

	# Take resources
	foreach($take as $type => $amount)
	{
		$statement = $db->prepare("
			UPDATE `col_resource_cards`
			SET `playerID` = :player
			WHERE
				`gameID` = :game AND
				`type` = :type AND
				`playerID` = :with
			LIMIT $amount
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $ID);
		$statement->bindValue("type", $type);
		$statement->bindValue("with", $withID);
		$statement->execute();
		$statement->closeCursor();
	}

	$statement = $db->prepare("
		UPDATE `col_trades`
		SET `status` = :status
		WHERE `ID` = :trade
	");
	$statement->bindValue("status", TRADE_ACCEPT);
	$statement->bindValue("trade", $tradeID);
	$statement->execute();
	$statement->closeCursor();

	$statement = $db->prepare("
		UPDATE `col_games`
		SET
			`activePlayerIndex` = :index,
			`substate` = :substate
		WHERE `ID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("index", $turnPlayerIndex);
	$statement->bindValue("substate", CHOOSE_ACTION);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "accepted the trade");
	if($deltaGiven < 0)
	{
		$deltaGiven *= -1;
		colonyMessage($gameID, $ID, "increased hand size by $deltaGiven cards");
		colonyMessage($gameID, $withID, "decreased hand size by $deltaGiven cards");
	}
	else if(0 < $deltaGiven)
	{
		colonyMessage($gameID, $ID, "decreased hand size by $deltaGiven cards");
		colonyMessage($gameID, $withID, "increased hand size by $deltaGiven cards");
	}
	else
	{
		colonyMessage($gameID, $ID, "did not change hand size");
		colonyMessage($gameID, $withID, "did not change hand size");
	}

	colonyAlertActivePlayer($gameID, $turnPlayerIndex);
?>
