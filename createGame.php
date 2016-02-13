<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate();

	if(!colonyPost("colorID") || !colonyPost("opponent1") || !colonyPost("opponent2") || !colonyPost("opponent3"))
		colonyError("colorID, opponent1, opponent2, and opponent3 must all be defined to create a game");
	$colorID = intval($_POST["colorID"]);

	# Verify all the opponents
	$opponents = array();
	for($i = 1; $i <= 3; ++$i)
	{
		$emailAddress = $_POST["opponent$i"];
		if("" === $emailAddress)
			continue;

		$statement = $db->prepare("
			SELECT
				`ID`,
				`displayName`
			FROM `col_players`
			WHERE `emailAddress` = :email
		");
		$statement->bindValue("email", $emailAddress);
		$statement->execute();

		$row = $statement->fetch();
		if(FALSE === $row)
			colonyError("$emailAddress is not an e-mail address of a registered player");

		$playerID = intval($row["ID"]);
		$displayName = htmlspecialchars($row["displayName"]);
		$statement->closeCursor();

		if($loggedIn["ID"] === $playerID)
			colonyError("You can't play as your own opponent");

		array_push($opponents, array("ID" => $playerID, "displayName" => $displayName, "emailAddress" => $emailAddress));
	}
	if(0 == count($opponents))
		colonyError("You must have at least one opponent");

	# Only support one board layout for now
	require("lib/gameTypes/standard.php");

	# The robber-tile ID will be filled in and updated after tiles are
	# inserted into the game
	$robberTileID = 0;
	$sideLength = 100;
	$width = 7 * sqrt(3) * $sideLength;
	$height = 11 * $sideLength;

	$statement = $db->prepare("
		INSERT INTO `col_games`
		SET
			`playerLimit` = :limit,
			`width` = :width,
			`height` = :height,
			`robberTileID` = '0',
			`roadLimit` = '15',
			`settlementLimit` = '5',
			`cityLimit` = '4'
	");
	$statement->bindValue("limit", 1 + count($opponents));
	$statement->bindValue("width", $width);
	$statement->bindValue("height", $height);
	$statement->execute();
	$statement->closeCursor();

	$gameID = $db->lastInsertID();

	# Host
	$statement = $db->prepare("
		INSERT INTO `col_playing`
		SET
			`gameID` = :game,
			`playerID` = :player,
			`colorID` = :color,
			`isJoined` = '1'
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $loggedIn["ID"]);
	$statement->bindValue("color", $colorID);
	$statement->execute();
	$statement->closeCursor();

	# Opponents
	foreach($opponents as $opponent)
	{
		$statement = $db->prepare("
			INSERT INTO `col_playing`
			SET
				`gameID` = :game,
				`playerID` = :player
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $opponent["ID"]);
		$statement->execute();
		$statement->closeCursor();
	}

	# Development cards
	foreach($developmentCards as $card)
	{
		$statement = $db->prepare("
			INSERT INTO `col_development_cards`
			SET
				`gameID` = :game,
				`type` = :type
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("type", $card);
		$statement->execute();
		$statement->closeCursor();
	}

	# Resource cards
	foreach($resourceCards as $card)
	{
		$statement = $db->prepare("
			INSERT INTO `col_resource_cards`
			SET
				`gameID` = :game,
				`type` = :type
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("type", $card);
		$statement->execute();
		$statement->closeCursor();
	}

	# Tiles
	foreach($tiles as $index => $tile)
	{
		$statement = $db->prepare("
			INSERT INTO `col_tiles`
			SET
				`gameID` = :game,
				`type` = :type,
				`diceRoll` = :roll,
				`x` = :x,
				`y` = :y
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("type", $tile["type"]);
		$statement->bindValue("roll", $tile["diceRoll"]);
		$statement->bindValue("x", sqrt(3) * $sideLength * $tile["x"] / 2);
		$statement->bindValue("y", 3 * $sideLength * $tile["y"] / 2);
		$statement->execute();
		$statement->closeCursor();

		$tiles[$index]["ID"] = $db->lastInsertID();
		if("Desert" === $tile["type"])
			$robberTileID = $db->lastInsertID();
	}

	# Towns
	foreach($towns as $index => $town)
	{
		$statement = $db->prepare("
			INSERT INTO `col_towns`
			SET
				`gameID` = :game,
				`tile1ID` = :tile1,
				`tile2ID` = :tile2,
				`tile3ID` = :tile3
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("tile1", $tiles[$town["tile1Index"]]["ID"]);
		$statement->bindValue("tile2", $tiles[$town["tile2Index"]]["ID"]);
		$statement->bindValue("tile3", $tiles[$town["tile3Index"]]["ID"]);
		$statement->execute();
		$statement->closeCursor();

		$towns[$index]["ID"] = $db->lastInsertID();
	}

	# Roads
	foreach($roads as $index => $road)
	{
		$statement = $db->prepare("
			INSERT INTO `col_roads`
			SET
				`gameID` = :game,
				`town1ID` = :town1,
				`town2ID` = :town2
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("town1", $towns[$road["town1Index"]]["ID"]);
		$statement->bindValue("town2", $towns[$road["town2Index"]]["ID"]);
		$statement->execute();
		$statement->closeCursor();

		$roads[$index]["ID"] = $db->lastInsertID();
	}

	# Ports
	foreach($ports as $index => $port)
	{
		$statement = $db->prepare("
			INSERT INTO `col_ports`
			SET
				`gameID` = :game,
				`town1ID` = :town1,
				`town2ID` = :town2,
				`tileID` = :tile,
				`amount` = :amount,
				`resource` = :resource
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("town1", $towns[$port["town1Index"]]["ID"]);
		$statement->bindValue("town2", $towns[$port["town2Index"]]["ID"]);
		$statement->bindValue("tile", $tiles[$port["tileIndex"]]["ID"]);
		$statement->bindValue("amount", $port["amount"]);
		$statement->bindValue("resource", $port["resource"]);
		$statement->execute();
		$statement->closeCursor();

		$ports[$index]["ID"] = $db->lastInsertID();
	}

	$statement = $db->prepare("
		UPDATE `col_games`
		SET `robberTileID` = :robber
		WHERE `ID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("robber", $robberTileID);
	$statement->execute();
	$statement->closeCursor();

	colonyMessage($gameID, $loggedIn["ID"], "has created game $gameID");
	$emailMessage = "You are invited to join <a href=\"" . colonyGameLink($gameID) . "\">game $gameID</a>.";
	foreach($opponents as $opponent)
		colonyAlertPlayer($opponent["displayName"], $opponent["emailAddress"], "Game $gameID", $emailMessage);

	header("Location: game.php?gameID=$gameID");
?>
