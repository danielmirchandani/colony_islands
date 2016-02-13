<?php
	// Do you have enough resources?
	if(colonyCheckResource($gameID, $ID, "Grain") < 1)
		colonyError("One Grain is required to buy a development card");
	if(colonyCheckResource($gameID, $ID, "Ore") < 1)
		colonyError("One Ore is required to buy a development card");
	if(colonyCheckResource($gameID, $ID, "Wool") < 1)
		colonyError("One Wool is required to buy a development card");

	colonyUseResource($gameID, $ID, "Grain", 1);
	colonyUseResource($gameID, $ID, "Ore", 1);
	colonyUseResource($gameID, $ID, "Wool", 1);

	$statement = $db->prepare("
		UPDATE `col_development_cards`
		SET
			`playerID` = :player,
			`turnBought` = :turn
		WHERE
			`gameID` = :game AND
			`playerID` = '0'
		ORDER BY `ID`
		LIMIT 1
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("turn", $currentTurn);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "bought a development card");

	if(!colonyCheckWin($gameID, $ID))
		colonySetSubstate($gameID, CHOOSE_ACTION);
?>
