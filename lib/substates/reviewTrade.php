<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to review a trade");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
		$statement = $db->prepare("
			SELECT
				`col_trades`.`ID`,
				`col_players`.`displayName`
			FROM `col_trades`
			LEFT JOIN `col_players` ON `col_trades`.`player1ID` = `col_players`.`ID`
			WHERE
				`gameID` = :game AND
				`player2ID` = :player2 AND
				`status` = :status
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player2", $loggedIn["ID"]);
		$statement->bindValue("status", TRADE_REVIEW);
		$statement->execute();

		$row = $statement->fetch();
		if(FALSE === $row)
			colonyError("No trades for you to review in game $gameID");

		$tradeID = intval($row["ID"]);
		$displayName = htmlspecialchars($row["displayName"]);
		$statement->closeCursor();

		$awayString = "";
		$forString = "";

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

		while(FALSE !== ($row = $statement->fetch()))
		{
			$amount = intval($row["amount"]);
			$type = htmlspecialchars($row["type"]);

			if($amount < 0)
				$awayString = $awayString . ("" === $awayString ? "" : ", ") . (-$amount) . " " . $type;
			else if(0 < $amount)
				$forString = $forString . ("" === $forString ? "" : ", ") . $amount . " " . $type;
		}
		$statement->closeCursor();
?>
<p><?php echo($displayName);?> would like to trade away <?php echo($awayString);?> for <?php echo($forString);?> from you.</p>
<form id="selectForm" method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="acceptTrade">
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Accept Trade">
	</div>
</form>
<form id="selectForm" method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="rejectTrade">
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Reject Trade">
	</div>
</form>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is reviewing a trade with another player.</p>
<?php
	}
?>
