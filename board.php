<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyStart();

	if(!colonyGet("gameID"))
		colonyError("Must provide a gameID");
	$gameID = intval($_GET["gameID"]);

	$select = "";
	if(colonyGet("select"))
	{
		$select = $_GET["select"];
		if(($select !== "road") && ($select !== "tile") && ($select !== "town"))
			colonyError("Can only select a road, tile, or town");
	}

	$probabilities = array(0 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 8 => 5, 9 => 4, 10 => 3, 11 => 2, 12 => 1);
	$tiles = array();
	$towns = array();
	$roads = array();
	$ports = array();

	$statement = $db->prepare("
		SELECT
			activePlayerIndex,
			height,
			robberTileID,
			substate,
			width
		FROM `col_games`
		WHERE `ID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("gameID $gameID doesn't exist");

	$activePlayerIndex = intval($row["activePlayerIndex"]);
	$gridHeight = doubleval($row["height"]);
	$gridWidth = doubleval($row["width"]);
	$robberTileID = intval($row["robberTileID"]);
	$substate = intval($row["substate"]);
	$statement->closeCursor();

	$gridX = -$gridWidth / 2;
	$gridY = -$gridHeight / 2;

	// Get tiles from the database
	$statement = $db->prepare("
		SELECT
			`ID`,
			`type`,
			`diceRoll`,
			`x`,
			`y`
		FROM `col_tiles`
		WHERE `gameID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$tileID = intval($row["ID"]);
		$type = htmlspecialchars($row["type"]);
		$diceRoll = intval($row["diceRoll"]);
		$x = doubleval($row["x"]);
		$y = doubleval($row["y"]);

		$newTile = array(
			"type" => $type,
			"x" => $x,
			"y" => $y
		);

		if($robberTileID === $tileID)
			$newTile["robber"] = true;

		if(0 !== $diceRoll)
		{
			$newTile["diceRoll"] = $diceRoll;
			$newTile["probability"] = $probabilities[$diceRoll];
		}

		$tiles[$tileID] = $newTile;
	}
	$statement->closeCursor();

	// Get towns from the database
	$statement = $db->prepare("
		SELECT
			`col_towns`.`ID`,
			`colorID`,
			`tile1ID`,
			`tile2ID`,
			`tile3ID`,
			`type`
		FROM `col_towns`
		LEFT JOIN `col_playing` ON
			`col_towns`.`playerID` = `col_playing`.`playerID` AND
			`col_towns`.`gameID` = `col_playing`.`gameID`
		WHERE `col_towns`.`gameID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$townID = intval($row["ID"]);
		// This can be null if no player is occupying this town
		$colorID = $row["colorID"];
		$tile1ID = intval($row["tile1ID"]);
		$tile2ID = intval($row["tile2ID"]);
		$tile3ID = intval($row["tile3ID"]);
		$type = intval($row["type"]);

		$newTown = array(
			"tile1ID" => $tile1ID,
			"tile2ID" => $tile2ID,
			"tile3ID" => $tile3ID,
			"x" => ($tiles[$tile1ID]["x"] + $tiles[$tile2ID]["x"] + $tiles[$tile3ID]["x"]) / 3,
			"y" => ($tiles[$tile1ID]["y"] + $tiles[$tile2ID]["y"] + $tiles[$tile3ID]["y"]) / 3
		);

		if(NULL !== $colorID)
		{
			$newTown["color"] = intval($colorID);
			$newTown["type"] = $type;
		}

		$towns[$townID] = $newTown;
	}
	$statement->closeCursor();

	// Get roads from the database
	$statement = $db->prepare("
		SELECT
			`col_roads`.`ID`,
			`colorID`,
			`town1ID`,
			`town2ID`
		FROM `col_roads`
		LEFT JOIN `col_playing` ON
			`col_roads`.`playerID` = `col_playing`.`playerID` AND
			`col_roads`.`gameID` = `col_playing`.`gameID`
		WHERE
			`col_roads`.`gameID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$roadID = intval($row["ID"]);
		// This can be null if no player is occupying this road
		$colorID = $row["colorID"];
		$town1ID = intval($row["town1ID"]);
		$town2ID = intval($row["town2ID"]);

		$town1 = $towns[$town1ID];
		$town2 = $towns[$town2ID];
		$x = ($town1["x"] + $town2["x"]) / 2;
		$y = ($town1["y"] + $town2["y"]) / 2;
		$angle = atan2($town2["y"] - $town1["y"], $town2["x"] - $town1["x"]) * 180 / pi();

		$newRoad = array(
			"angle" => $angle,
			"x" => $x,
			"y" => $y
		);

		if(NULL !== $colorID)
			$newRoad["color"] = intval($colorID);

		$roads[$roadID] = $newRoad;
	}
	$statement->closeCursor();

	// Get ports from the database
	$statement = $db->prepare("
		SELECT
			`ID`,
			`town1ID`,
			`town2ID`,
			`tileID`,
			`amount`,
			`resource`
		FROM `col_ports`
		WHERE `gameID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$portID = intval($row["ID"]);
		$town1ID = intval($row["town1ID"]);
		$town2ID = intval($row["town2ID"]);
		$tileID = intval($row["tileID"]);
		$amount = intval($row["amount"]);
		$resource = htmlspecialchars($row["resource"]);

		// This might need to be fixed so towns don't have to be in a specific
		// order
		$town1 = $towns[$town1ID];
		$town2 = $towns[$town2ID];
		$tile = $tiles[$tileID];
		$angle = atan2($town2["y"] - $town1["y"], $town2["x"] - $town1["x"]) * 180 / pi();

		$ports[$portID] = array(
			"amount" => $amount,
			"resource" => $resource,
			"angle" => $angle,
			"x" => $tile["x"],
			"y" => $tile["y"]
		);
	}
	$statement->closeCursor();

	$myPlayerIndex = NULL;

	$statement = $db->prepare("
		SELECT playIndex
		FROM col_playing
		WHERE
			gameID = :game AND
			playerID = :playerID
		ORDER BY playIndex
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("playerID", $loggedIn["ID"]);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
		$myPlayerIndex = intval($row["playIndex"]);
	$statement->closeCursor();

	$output = array("tiles" => $tiles, "towns" => $towns, "roads" => $roads, "ports" => $ports);
	if($activePlayerIndex === $myPlayerIndex)
	{
		if((BUILD_ROAD === $substate) || (ROAD_BUILDING_1 === $substate) || (ROAD_BUILDING_2 === $substate))
			$output["select"] = "road";
		else if(MOVE_ROBBER === $substate)
			$output["select"] = "tile";
		else if((BUILD_CITY === $substate) || (BUILD_SETTLEMENT === $substate))
			$output["select"] = "town";
	}

	header('Content-Type: application/json');
	print json_encode($output);
	colonyEnd();
?>
