<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to reject a trade");
	if(REVIEW_TRADE !== $substate)
		colonyError("Game $gameID must be in substate REVIEW_TRADE to reject a trade");

	$statement = $db->prepare("
		SELECT
			`ID`,
			`player1ID`
		FROM `col_trades`
		WHERE
			`gameID` = :game AND
			`player2ID` = :player AND
			`status` = :status
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("status", TRADE_REVIEW);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("No trades for you to reject in game $gameID");

	$tradeID = intval($row["ID"]);
	$withID = intval($row["player1ID"]);
	$statement->closeCursor();

	$statement = $db->prepare("
		UPDATE `col_trades`
		SET `status` = :status
		WHERE `ID` = :trade
	");
	$statement->bindValue("status", TRADE_REJECT);
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

	colonyMessage($gameID, $ID, "rejected the trade");
	colonyAlertActivePlayer($gameID, $turnPlayerIndex);
?>
