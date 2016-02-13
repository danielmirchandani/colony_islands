<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to end the turn");
	if(CHOOSE_ACTION !== $substate)
		colonyError("Game $gameID must be in substate CHOOSE_ACTION to end the turn");

	$nextTurn = $currentTurn + 1;
	$nextTurnPlayerIndex = ($turnPlayerIndex + 1) % $playerLimit;

	$statement = $db->prepare("
		UPDATE `col_games`
		SET
			`activePlayerIndex` = :activePlayer,
			`currentTurn` = :turn,
			`state` = :state,
			`turnPlayerIndex` = :turnPlayer
		WHERE `ID` = :game
	");
	$statement->bindValue("activePlayer", $nextTurnPlayerIndex);
	$statement->bindValue("turn", $nextTurn);
	$statement->bindValue("state", BEFORE_ROLL);
	$statement->bindValue("turnPlayer", $nextTurnPlayerIndex);
	$statement->bindValue("game", $gameID);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "ended turn $currentTurn");
	if($activePlayerIndex != $nextTurnPlayerIndex)
		colonyAlertActivePlayer($gameID, $nextTurnPlayerIndex);
?>
