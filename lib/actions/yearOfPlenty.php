<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to celebrate a year of plenty");
	if(CELEBRATE_YEAR_OF_PLENTY !== $substate)
		colonyError("Game $gameID must be in substate CELEBRATE_YEAR_OF_PLENTY to celebrate a year of plenty");

	$resourceLeft = 0;
	$resourceTotal = 0;
	$resourceTypes = array("Brick" => 0, "Grain" => 0, "Lumber" => 0, "Ore" => 0, "Wool" => 0);
	foreach($resourceTypes as $resourceType => $resourceCount)
	{
		if(!colonyPost($resourceType))
			colonyError("Must provide an amount of $resourceType to celebrate a year of plenty");
		$resourceTypes[$resourceType] = intval($_POST[$resourceType]);
		$currentResourceLeft = colonyCheckResource($gameID, 0, $resourceType);
		$resourceLeft += $currentResourceLeft;
		if($currentResourceLeft < $resourceTypes[$resourceType])
			colonyError("Must specify at most $resourceType in the bank to celebrate a year of plenty");
		$resourceTotal += $resourceTypes[$resourceType];
	}
	$resourceLimit = min(2, $resourceLeft);
	if($resourceLimit !== $resourceTotal)
		colonyError("Exactly $resourceLimit resource card(s) must be selected to celebrate a year of plenty");

	colonyMessage($gameID, $ID, "celebrated a year of plenty");
	foreach($resourceTypes as $resourceType => $resourceCount)
	{
		if(0 === $resourceCount)
			continue;
		colonyGetResource($gameID, $ID, $resourceType, $resourceCount);
		colonyMessage($gameID, $ID, "received $resourceCount $resourceType");
	}

	$statement = $db->prepare("
		UPDATE `col_games`
		SET `substate` = :substate
		WHERE `ID` = :game
	");
	$statement->bindValue("substate", CHOOSE_ACTION);
	$statement->bindValue("game", $gameID);
	$statement->execute();
	$statement->closeCursor();
?>
