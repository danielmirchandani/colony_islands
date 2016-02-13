<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to move the robber");
	if(MOVE_ROBBER !== $substate)
		colonyError("Game $gameID must be in substate MOVE_ROBBER to move the robber");

	if(!colonyPost("tileID"))
		colonyError("Must provide a tileID to move the robber");
	$tileID = intval($_POST["tileID"]);

	if($robberTileID == $tileID)
		colonyError("A new location must be selected to move the robber");

	$statement = $db->prepare("
		SELECT '1'
		FROM col_tiles
		WHERE
			`gameID` = :game AND
			`ID` = :tile AND
			`type` != :desert AND
			`type` != :water
	");
	$statement->bindValue("desert", "Desert");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("tile", $tileID);
	$statement->bindValue("water", "Water");
	$statement->execute();

	if(FALSE === $statement->fetch())
		colonyError("A resource-producing tile must be selected to move the robber");
	$statement->closeCursor();

	$statement = $db->prepare("
		UPDATE col_games
		SET `robberTileID` = :tile
		WHERE `ID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("tile", $tileID);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $ID, "moved the robber to tile $tileID");

	$statement = $db->prepare("
		SELECT '1'
		FROM col_towns
		WHERE
			`gameID` = :game AND
			(
				`tile1ID` = :tile OR
				`tile2ID` = :tile OR
				`tile3ID` = :tile
			) AND
			`playerID` != '0' AND
			`playerID` != :player
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->bindValue("tile", $tileID);
	$statement->execute();

	$noResourcesToSteal = (FALSE === $statement->fetch());
	$statement->closeCursor();

	if($noResourcesToSteal)
		colonySetSubstate($gameID, CHOOSE_ACTION);
	else
		colonySetSubstate($gameID, STEAL_RESOURCE);
?>
