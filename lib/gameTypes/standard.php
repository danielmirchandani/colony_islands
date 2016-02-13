<?php
	$tiles = array(
		array("x" =>  0, "y" =>  0), // 0
		array("x" =>  1, "y" => -1),
		array("x" =>  2, "y" =>  0),
		array("x" =>  1, "y" =>  1),
		array("x" => -1, "y" =>  1),
		array("x" => -2, "y" =>  0),
		array("x" => -1, "y" => -1), // 6
		array("x" =>  0, "y" => -2),
		array("x" =>  2, "y" => -2),
		array("x" =>  3, "y" => -1),
		array("x" =>  4, "y" =>  0),
		array("x" =>  3, "y" =>  1),
		array("x" =>  2, "y" =>  2), // 12
		array("x" =>  0, "y" =>  2),
		array("x" => -2, "y" =>  2),
		array("x" => -3, "y" =>  1),
		array("x" => -4, "y" =>  0),
		array("x" => -3, "y" => -1),
		array("x" => -2, "y" => -2), // 18
		array("x" => -1, "y" => -3),
		array("x" =>  1, "y" => -3),
		array("x" =>  3, "y" => -3),
		array("x" =>  4, "y" => -2),
		array("x" =>  5, "y" => -1),
		array("x" =>  6, "y" =>  0), // 24
		array("x" =>  5, "y" =>  1),
		array("x" =>  4, "y" =>  2),
		array("x" =>  3, "y" =>  3),
		array("x" =>  1, "y" =>  3),
		array("x" => -1, "y" =>  3),
		array("x" => -3, "y" =>  3), // 30
		array("x" => -4, "y" =>  2),
		array("x" => -5, "y" =>  1),
		array("x" => -6, "y" =>  0),
		array("x" => -5, "y" => -1),
		array("x" => -4, "y" => -2),
		array("x" => -3, "y" => -3), // 36
	);

	$towns = array(
		array("tile1Index" =>  0, "tile2Index" =>  6, "tile3Index" =>  1), // 0
		array("tile1Index" =>  0, "tile2Index" =>  1, "tile3Index" =>  2),
		array("tile1Index" =>  0, "tile2Index" =>  2, "tile3Index" =>  3),
		array("tile1Index" =>  0, "tile2Index" =>  3, "tile3Index" =>  4),
		array("tile1Index" =>  0, "tile2Index" =>  4, "tile3Index" =>  5),
		array("tile1Index" =>  0, "tile2Index" =>  5, "tile3Index" =>  6),
		array("tile1Index" =>  1, "tile2Index" =>  6, "tile3Index" =>  7), // 6
		array("tile1Index" =>  1, "tile2Index" =>  7, "tile3Index" =>  8),
		array("tile1Index" =>  1, "tile2Index" =>  8, "tile3Index" =>  9),
		array("tile1Index" =>  2, "tile2Index" =>  1, "tile3Index" =>  9),
		array("tile1Index" =>  2, "tile2Index" =>  9, "tile3Index" => 10),
		array("tile1Index" =>  2, "tile2Index" => 10, "tile3Index" => 11),
		array("tile1Index" =>  3, "tile2Index" =>  2, "tile3Index" => 11), // 12
		array("tile1Index" =>  3, "tile2Index" => 11, "tile3Index" => 12),
		array("tile1Index" =>  3, "tile2Index" => 12, "tile3Index" => 13),
		array("tile1Index" =>  4, "tile2Index" =>  3, "tile3Index" => 13),
		array("tile1Index" =>  4, "tile2Index" => 13, "tile3Index" => 14),
		array("tile1Index" =>  4, "tile2Index" => 14, "tile3Index" => 15),
		array("tile1Index" =>  5, "tile2Index" =>  4, "tile3Index" => 15), // 18
		array("tile1Index" =>  5, "tile2Index" => 15, "tile3Index" => 16),
		array("tile1Index" =>  5, "tile2Index" => 16, "tile3Index" => 17),
		array("tile1Index" =>  6, "tile2Index" =>  5, "tile3Index" => 17),
		array("tile1Index" =>  6, "tile2Index" => 17, "tile3Index" => 18),
		array("tile1Index" =>  6, "tile2Index" => 18, "tile3Index" =>  7),
		array("tile1Index" =>  7, "tile2Index" => 19, "tile3Index" => 20), // 24
		array("tile1Index" =>  8, "tile2Index" =>  7, "tile3Index" => 20),
		array("tile1Index" =>  8, "tile2Index" => 20, "tile3Index" => 21),
		array("tile1Index" =>  8, "tile2Index" => 21, "tile3Index" => 22),
		array("tile1Index" =>  8, "tile2Index" => 22, "tile3Index" =>  9),
		array("tile1Index" =>  9, "tile2Index" => 22, "tile3Index" => 23), // 29
		array("tile1Index" => 10, "tile2Index" =>  9, "tile3Index" => 23),
		array("tile1Index" => 10, "tile2Index" => 23, "tile3Index" => 24),
		array("tile1Index" => 10, "tile2Index" => 24, "tile3Index" => 25),
		array("tile1Index" => 10, "tile2Index" => 25, "tile3Index" => 11),
		array("tile1Index" => 11, "tile2Index" => 25, "tile3Index" => 26), // 34
		array("tile1Index" => 12, "tile2Index" => 11, "tile3Index" => 26),
		array("tile1Index" => 12, "tile2Index" => 26, "tile3Index" => 27),
		array("tile1Index" => 12, "tile2Index" => 27, "tile3Index" => 28),
		array("tile1Index" => 12, "tile2Index" => 28, "tile3Index" => 13),
		array("tile1Index" => 13, "tile2Index" => 28, "tile3Index" => 29), // 39
		array("tile1Index" => 14, "tile2Index" => 13, "tile3Index" => 29),
		array("tile1Index" => 14, "tile2Index" => 29, "tile3Index" => 30),
		array("tile1Index" => 14, "tile2Index" => 30, "tile3Index" => 31),
		array("tile1Index" => 14, "tile2Index" => 31, "tile3Index" => 15),
		array("tile1Index" => 15, "tile2Index" => 31, "tile3Index" => 32), // 44
		array("tile1Index" => 16, "tile2Index" => 15, "tile3Index" => 32),
		array("tile1Index" => 16, "tile2Index" => 32, "tile3Index" => 33),
		array("tile1Index" => 16, "tile2Index" => 33, "tile3Index" => 34),
		array("tile1Index" => 16, "tile2Index" => 34, "tile3Index" => 17),
		array("tile1Index" => 17, "tile2Index" => 34, "tile3Index" => 35), // 49
		array("tile1Index" => 18, "tile2Index" => 17, "tile3Index" => 35),
		array("tile1Index" => 18, "tile2Index" => 35, "tile3Index" => 36),
		array("tile1Index" => 18, "tile2Index" => 36, "tile3Index" => 19),
		array("tile1Index" => 18, "tile2Index" => 19, "tile3Index" =>  7),
	);

	$roads = array(
		array("town1Index" =>  0, "town2Index" =>  1), // 0
		array("town1Index" =>  1, "town2Index" =>  2),
		array("town1Index" =>  2, "town2Index" =>  3),
		array("town1Index" =>  3, "town2Index" =>  4),
		array("town1Index" =>  4, "town2Index" =>  5),
		array("town1Index" =>  5, "town2Index" =>  0),
		array("town1Index" =>  0, "town2Index" =>  6), // 6
		array("town1Index" =>  1, "town2Index" =>  9),
		array("town1Index" =>  2, "town2Index" => 12),
		array("town1Index" =>  3, "town2Index" => 15),
		array("town1Index" =>  4, "town2Index" => 18),
		array("town1Index" =>  5, "town2Index" => 21),
		array("town1Index" =>  6, "town2Index" =>  7), // 12
		array("town1Index" =>  7, "town2Index" =>  8),
		array("town1Index" =>  8, "town2Index" =>  9),
		array("town1Index" =>  9, "town2Index" => 10),
		array("town1Index" => 10, "town2Index" => 11),
		array("town1Index" => 11, "town2Index" => 12),
		array("town1Index" => 12, "town2Index" => 13), // 18
		array("town1Index" => 13, "town2Index" => 14),
		array("town1Index" => 14, "town2Index" => 15),
		array("town1Index" => 15, "town2Index" => 16),
		array("town1Index" => 16, "town2Index" => 17),
		array("town1Index" => 17, "town2Index" => 18),
		array("town1Index" => 18, "town2Index" => 19), // 24
		array("town1Index" => 19, "town2Index" => 20),
		array("town1Index" => 20, "town2Index" => 21),
		array("town1Index" => 21, "town2Index" => 22),
		array("town1Index" => 22, "town2Index" => 23),
		array("town1Index" => 23, "town2Index" =>  6),
		array("town1Index" =>  7, "town2Index" => 25), // 30
		array("town1Index" =>  8, "town2Index" => 28),
		array("town1Index" => 10, "town2Index" => 30),
		array("town1Index" => 11, "town2Index" => 33),
		array("town1Index" => 13, "town2Index" => 35),
		array("town1Index" => 14, "town2Index" => 38),
		array("town1Index" => 16, "town2Index" => 40), // 36
		array("town1Index" => 17, "town2Index" => 43),
		array("town1Index" => 19, "town2Index" => 45),
		array("town1Index" => 20, "town2Index" => 48),
		array("town1Index" => 22, "town2Index" => 50),
		array("town1Index" => 23, "town2Index" => 53),
		array("town1Index" => 24, "town2Index" => 25), // 42
		array("town1Index" => 25, "town2Index" => 26),
		array("town1Index" => 26, "town2Index" => 27),
		array("town1Index" => 27, "town2Index" => 28),
		array("town1Index" => 28, "town2Index" => 29),
		array("town1Index" => 29, "town2Index" => 30),
		array("town1Index" => 30, "town2Index" => 31), // 48
		array("town1Index" => 31, "town2Index" => 32),
		array("town1Index" => 32, "town2Index" => 33),
		array("town1Index" => 33, "town2Index" => 34),
		array("town1Index" => 34, "town2Index" => 35),
		array("town1Index" => 35, "town2Index" => 36),
		array("town1Index" => 36, "town2Index" => 37), // 54
		array("town1Index" => 37, "town2Index" => 38),
		array("town1Index" => 38, "town2Index" => 39),
		array("town1Index" => 39, "town2Index" => 40),
		array("town1Index" => 40, "town2Index" => 41),
		array("town1Index" => 41, "town2Index" => 42),
		array("town1Index" => 42, "town2Index" => 43), // 60
		array("town1Index" => 43, "town2Index" => 44),
		array("town1Index" => 44, "town2Index" => 45),
		array("town1Index" => 45, "town2Index" => 46),
		array("town1Index" => 46, "town2Index" => 47),
		array("town1Index" => 47, "town2Index" => 48),
		array("town1Index" => 48, "town2Index" => 49), // 66
		array("town1Index" => 49, "town2Index" => 50),
		array("town1Index" => 50, "town2Index" => 51),
		array("town1Index" => 51, "town2Index" => 52),
		array("town1Index" => 52, "town2Index" => 53),
		array("town1Index" => 53, "town2Index" => 24),
	);

	// Randomly decide between "even" ports and "odd" ports
	if(0 == mt_rand(0, 1))
	{
		$ports = array(
			array("town1Index" => 24, "town2Index" => 25, "tileIndex" => 20),
			array("town1Index" => 28, "town2Index" => 29, "tileIndex" => 22),
			array("town1Index" => 31, "town2Index" => 32, "tileIndex" => 24),
			array("town1Index" => 34, "town2Index" => 35, "tileIndex" => 26),
			array("town1Index" => 38, "town2Index" => 39, "tileIndex" => 28),
			array("town1Index" => 41, "town2Index" => 42, "tileIndex" => 30),
			array("town1Index" => 44, "town2Index" => 45, "tileIndex" => 32),
			array("town1Index" => 48, "town2Index" => 49, "tileIndex" => 34),
			array("town1Index" => 51, "town2Index" => 52, "tileIndex" => 36),
		);
	}
	else
	{
		$ports = array(
			array("town1Index" => 53, "town2Index" => 24, "tileIndex" => 19),
			array("town1Index" => 26, "town2Index" => 27, "tileIndex" => 21),
			array("town1Index" => 29, "town2Index" => 30, "tileIndex" => 23),
			array("town1Index" => 33, "town2Index" => 34, "tileIndex" => 25),
			array("town1Index" => 36, "town2Index" => 37, "tileIndex" => 27),
			array("town1Index" => 39, "town2Index" => 40, "tileIndex" => 29),
			array("town1Index" => 43, "town2Index" => 44, "tileIndex" => 31),
			array("town1Index" => 46, "town2Index" => 47, "tileIndex" => 33),
			array("town1Index" => 49, "town2Index" => 50, "tileIndex" => 35),
		);
	}

	$developmentCards = array(
		// 14 Soldier
		"Soldier", "Soldier", "Soldier", "Soldier", "Soldier", "Soldier", "Soldier", "Soldier", "Soldier", "Soldier",
		"Soldier", "Soldier", "Soldier", "Soldier",
		// 6 progress cards
		"Road Building", "Road Building", "Year of Plenty", "Year of Plenty", "Monopoly", "Monopoly",
		// 5 victory point cards
		"Victory Point", "Victory Point", "Victory Point", "Victory Point", "Victory Point"
	);

	$resourceCards = array(
		// 19 Brick
		"Brick", "Brick", "Brick", "Brick", "Brick", "Brick", "Brick", "Brick", "Brick", "Brick",
		"Brick", "Brick", "Brick", "Brick", "Brick", "Brick", "Brick", "Brick", "Brick",
		// 19 Grain
		"Grain", "Grain", "Grain", "Grain", "Grain", "Grain", "Grain", "Grain", "Grain", "Grain",
		"Grain", "Grain", "Grain", "Grain", "Grain", "Grain", "Grain", "Grain", "Grain",
		// 19 Lumber
		"Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber",
		"Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber", "Lumber",
		// 19 Ore
		"Ore", "Ore", "Ore", "Ore", "Ore", "Ore", "Ore", "Ore", "Ore", "Ore",
		"Ore", "Ore", "Ore", "Ore", "Ore", "Ore", "Ore", "Ore", "Ore",
		// 19 Wool
		"Wool", "Wool", "Wool", "Wool", "Wool", "Wool", "Wool", "Wool", "Wool", "Wool",
		"Wool", "Wool", "Wool", "Wool", "Wool", "Wool", "Wool", "Wool", "Wool"
	);

	$landTileRolls = array(11, 3, 6, 5, 4, 9, 10, 8, 4, 11, 12, 9, 10, 8, 3, 6, 2, 5);
	$landTileTypes = array(
		"Desert",
		"Brick", "Brick", "Brick",
		"Grain", "Grain", "Grain", "Grain",
		"Lumber", "Lumber", "Lumber", "Lumber",
		"Ore", "Ore", "Ore",
		"Wool", "Wool", "Wool", "Wool",
	);
	$portTileTypes = array(
		array("amount" => 2, "resource" => "Brick"),
		array("amount" => 2, "resource" => "Grain"),
		array("amount" => 2, "resource" => "Lumber"),
		array("amount" => 2, "resource" => "Ore"),
		array("amount" => 2, "resource" => "Wool"),
		array("amount" => 3, "resource" => "Any"),
		array("amount" => 3, "resource" => "Any"),
		array("amount" => 3, "resource" => "Any"),
		array("amount" => 3, "resource" => "Any")
	);

	shuffle($developmentCards);
	shuffle($landTileTypes);
	shuffle($portTileTypes);

	// Fill in diceRoll and type for land tiles
	$diceRollIndex = 0;
	for($i = 0; $i < 19; ++$i)
	{
		if("Desert" !== $landTileTypes[$i])
		{
			$tiles[$i]["diceRoll"] = $landTileRolls[$diceRollIndex];
			++$diceRollIndex;
		}
		else
		{
			$tiles[$i]["diceRoll"] = 0;
		}
		$tiles[$i]["type"] = $landTileTypes[$i];
	}

	// Fill in diceRoll and type for water tiles
	for($i = 19; $i < 37; ++$i)
	{
		$tiles[$i]["diceRoll"] = 0;
		$tiles[$i]["type"] = "Water";
	}

	// Fill in amount and resource for ports
	for($i = 0; $i < 9; ++$i)
	{
		$ports[$i]["amount"] = $portTileTypes[$i]["amount"];
		$ports[$i]["resource"] = $portTileTypes[$i]["resource"];
	}
?>
