<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to trade with a port");
	if(MARITIME_TRADE !== $substate)
		colonyError("Game $gameID must be in substate MARITIME_TRADE to trade with a port");

	if(!colonyPost("amount"))
		colonyError("Must provide a amount to trade with a port");
	$amount = intval($_POST["amount"]);
	if(($amount < 2) || (4 < $amount))
		colonyError("Amount must be 2, 3, or 4 to trade with a port");

	if(!colonyPost("payment"))
		colonyError("Must provide a payment to trade with a port");
	$payment = $_POST["payment"];
	if(("Brick" !== $payment) && ("Grain" !== $payment) && ("Lumber" !== $payment) && ("Ore" !== $payment) && ("Wool" !== $payment))
		colonyError("Payment must be Brick, Grain, Lumber, Ore, or Wool to trade with a port");

	if(!colonyPost("receive"))
		colonyError("Must provide a receive to trade with a port");
	$receive = $_POST["receive"];
	if(("Brick" !== $receive) && ("Grain" !== $receive) && ("Lumber" !== $receive) && ("Ore" !== $receive) && ("Wool" !== $receive))
		colonyError("Receive must be Brick, Grain, Lumber, Ore, or Wool to trade with a port");

	if($payment === $receive)
			colonyError("Must not trade $payment for itself");

	if(4 !== $amount)
	{
		$statement = $db->prepare("
			SELECT '1'
			FROM `col_ports`
			LEFT JOIN `col_towns` as `town1` ON `col_ports`.`town1ID` = `town1`.`ID`
			LEFT JOIN `col_towns` as `town2` ON `col_ports`.`town2ID` = `town2`.`ID`
			WHERE
				`col_ports`.`gameID` = :game AND
				`col_ports`.`amount` = :amount AND
				(
					`col_ports`.`resource` = :payment OR
					`col_ports`.`resource` = 'Any'
				) AND
				(
					`town1`.`playerID` = :player OR
					`town2`.`playerID` = :player
				)
		");
		$statement->bindValue("amount", $amount);
		$statement->bindValue("game", $gameID);
		$statement->bindValue("payment", $payment);
		$statement->bindValue("player", $ID);
		$statement->execute();

		if(FALSE === $statement->fetch())
			colonyError("A controlled town-point must be on the port to trade with a port");
		$statement->closeCursor();
	}

	// Do you have enough resources?
	if(colonyCheckResource($gameID, $ID, $payment) < $amount)
		colonyError("You don't have enough $payment to trade with a port");

	// Does the game have enough resources?
	if(colonyCheckResource($gameID, 0, $receive) < 1)
		colonyError("There aren't enough $receive left to trade with a port");

	colonyUseResource($gameID, $ID, $payment, $amount);
	colonyGetResource($gameID, $ID, $receive, 1);
	colonyMessage($gameID, $ID, "traded $amount $payment to receive one $receive with a port");

	colonySetSubstate($gameID, CHOOSE_ACTION);
?>
