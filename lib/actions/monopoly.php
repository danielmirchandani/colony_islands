<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to monopolize a resource");
	if(MONOPOLIZE !== $substate)
		colonyError("Game $gameID must be in substate MONOPOLIZE to monopolize a resource");

	if(!colonyPost("resource"))
		colonyError("Must provide a resource to monopolize a resource");
	$resource = $_POST["resource"];
	if(("Brick" !== $resource) && ("Grain" !== $resource) && ("Lumber" !== $resource) && ("Ore" !== $resource) && ("Wool" !== $resource))
		colonyError("Resource must be Brick, Grain, Lumber, Ore, or Wool to monopolize a resource");

	# Get the number controlled by opponents
	$statement = $db->prepare("
		SELECT
			`playerID`,
			COUNT(*) as `resourceCount`
		FROM `col_resource_cards`
		WHERE
			`gameID` = :game AND
			`type` = :type AND
			`playerID` != :player AND
			`playerID` != 0
		GROUP BY `playerID`
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("type", $resource);
	$statement->execute();

	$losses = array();
	$total = 0;
	while(FALSE !== ($row = $statement->fetch()))
	{
		$playerID = intval($row["playerID"]);
		$amount = intval($row["resourceCount"]);

		$losses[$playerID] = $amount;
		$total += $amount;
	}
	$statement->closeCursor();

	$statement = $db->prepare("
		UPDATE `col_resource_cards`
		SET `playerID` = :player
		WHERE
			`gameID` = :game AND
			`type` = :type AND
			`playerID` != :player AND
			`playerID` != '0'
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("type", $resource);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "monopolized $total $resource");
	foreach($losses as $playerID => $amount)
		colonyMessage($gameID, $playerID, "lost $amount $resource");

	colonySetSubstate($gameID, CHOOSE_ACTION);
?>
