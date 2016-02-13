<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to build a city");
	if(BUILD_CITY !== $substate)
		colonyError("Game $gameID must be in substate BUILD_SETTLEMENT to build a city");

	if(!colonyPost("townID"))
		colonyError("Must provide a townID to build a city");
	$townID = intval($_POST["townID"]);

	# Find a settlement at the same point to upgrade
	$statement = $db->prepare("
		SELECT '1'
		FROM `col_towns`
		WHERE
			`ID` = :town AND
			`type` = '1' AND
			`playerID` = :player
	");
	$statement->bindValue("player", $ID);
	$statement->bindValue("town", $townID);
	$statement->execute();

	if(FALSE === $statement->fetch())
		colonyError("A controlled town-point with a settlement must be selected to build a city");
	$statement->closeCursor();

	# Make sure too many cities haven't been built
	$statement = $db->prepare("
		SELECT COUNT(*) as `cityCount`
		FROM `col_towns`
		WHERE
			`gameID` = :game AND
			`playerID` = :player AND
			`type` = '2'
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();

	$row = $statement->fetch();
	$cityCount = intval($row["cityCount"]);
	if($cityLimit === $cityCount)
		colonyError("Players must have less cities than the limit of $cityLimit to build a city");
	$statement->closeCursor();

	# Do you have enough resources?
	if(colonyCheckResource($gameID, $ID, "Grain") < 2)
		colonyError("Two Grain is required to build a city");
	if(colonyCheckResource($gameID, $ID, "Ore") < 3)
		colonyError("Three Ore is required to build a city");

	colonyUseResource($gameID, $ID, "Grain", 2);
	colonyUseResource($gameID, $ID, "Ore", 3);

	$statement = $db->prepare("
		UPDATE `col_towns`
		SET
			`playerID` = :player,
			`type` = '2'
		WHERE `ID` = :town
	");
	$statement->bindValue("player", $ID);
	$statement->bindValue("town", $townID);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "built a city at point $townID");

	if(!colonyCheckWin($gameID, $ID))
		colonySetSubstate($gameID, CHOOSE_ACTION);
?>
