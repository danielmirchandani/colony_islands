<?php
	require("config.php");
	require("PasswordHash.php");

	# Composer autoloader
	require(__DIR__ . "/../vendor/autoload.php");

	# Colors players can pick (These color names have to correspond to the
	# colors in colony.css)
	$colors = array(
		1 => "Red",
		2 => "Green",
		3 => "Blue",
		4 => "Yellow",
		5 => "Cyan",
		6 => "Magenta",
	);

	# Themes players can pick
	$themes = array(
		0 => "classic",
		1 => "dark",
		2 => "light",
	);

	# These are the major states of the game
	define("WAITING_FOR_PLAYERS", 0);
	define("FIRST_CHOICE", 1);
	define("SECOND_CHOICE", 2);
	define("BEFORE_ROLL", 3);
	define("AFTER_ROLL", 4);
	define("COMPLETE", 5);

	# These are the minor steps of the game
	define("NO_SUBSTATE", 0);
	define("BUILD_CITY", 1);
	define("BUILD_ROAD", 2);
	define("BUILD_SETTLEMENT", 3);
	define("CHOOSE_ACTION", 4);
	define("DISCARD_RESOURCES", 5);
	define("DOMESTIC_TRADE", 6);
	define("MARITIME_TRADE", 7);
	define("MOVE_ROBBER", 8);
	define("STEAL_RESOURCE", 9);
	define("USE_DEVELOPMENT_CARD", 10);
	define("ROAD_BUILDING_1", 11);
	define("ROAD_BUILDING_2", 12);
	define("CELEBRATE_YEAR_OF_PLENTY", 13);
	define("MONOPOLIZE", 14);
	define("REVIEW_TRADE", 15);

	# These are steps of domestic trading
	define("TRADE_REVIEW", 0);
	define("TRADE_ACCEPT", 1);
	define("TRADE_REJECT", 2);

	define("MONOPOLY", 0);
	define("ROAD_BUILDING", 1);
	define("SOLDIER", 2);
	define("VICTORY_POINT", 3);
	define("YEAR_OF_PLENTY", 4);
	$DEVELOPMENT_CARDS = array(
		MONOPOLY => "Monopoly",
		ROAD_BUILDING => "Road Building",
		SOLDIER => "Soldier",
		VICTORY_POINT => "Victory Point",
		YEAR_OF_PLENTY => "Year of Plenty"
	);

	define("BRICK", 0);
	define("GRAIN", 1);
	define("LUMBER", 2);
	define("ORE", 3);
	define("WOOL", 4);
	$RESOURCE_CARDS = array(
		BRICK => "Brick",
		GRAIN => "Grain",
		LUMBER => "Lumber",
		ORE => "Ore",
		WOOL => "Wool"
	);

	# For funny pictures when hovering over cards
	$CARD_PATTERNS = array(
		'/Brick/',
		'/Grain/',
		'/Lumber/',
		'/Ore/',
		'/Wool/',
		"/Monopoly/",
		"/Road Building/",
		"/Soldier/",
		"/Victory Point/",
		"/Year of Plenty/"
	);
	# The meaning of the ALT attribute for an IMG tag is how to replace the
	# image with equivalent text. These are purely decorative, so they have
	# empty-string alternative text.
	$CARD_IMAGE_HTML = array(
		'<a class="hoverShowIMG" href="images/brick.jpg"><img alt="" src="images/brick.jpg">Brick</a>',
		'<a class="hoverShowIMG" href="images/grain.jpg"><img alt="" src="images/grain.jpg">Grain</a>',
		'<a class="hoverShowIMG" href="images/lumber.jpg"><img alt="" src="images/lumber.jpg">Lumber</a>',
		'<a class="hoverShowIMG" href="images/ore.jpg"><img alt="" src="images/ore.jpg">Ore</a>',
		'<a class="hoverShowIMG" href="images/wool.jpg"><img alt="" src="images/wool.jpg">Wool</a>',
		'<a class="hoverShowIMG" href="images/monopoly.jpg"><img alt="" src="images/monopoly.jpg">Monopoly</a>',
		'<a class="hoverShowIMG" href="images/roadBuilding.jpg"><img alt="" src="images/roadBuilding.jpg">Road Building</a>',
		'<a class="hoverShowIMG" href="images/soldier.jpg"><img alt="" src="images/soldier.jpg">Soldier</a>',
		'<a class="hoverShowIMG" href="images/victoryPoint.jpg"><img alt="" src="images/victoryPoint.jpg">Victory Point</a>',
		'<a class="hoverShowIMG" href="images/yearOfPlenty.jpg"><img alt="" src="images/yearOfPlenty.jpg">Year of Plenty</a>'
	);

	# This keeps track of output-buffering so colonyError knows if to clear
	# the buffer or not
	$outputBufferStarted = FALSE;

	function colonyAlertActivePlayer($gameID, $activePlayerIndex)
	{
		global $db;

		$statement = $db->prepare("
			SELECT
				`col_players`.`displayName`,
				`col_players`.`emailAddress`
			FROM `col_playing`
			LEFT JOIN `col_players` ON `col_playing`.`playerID` = col_players.`ID`
			WHERE
				`col_playing`.`gameID` = :game AND
				`col_playing`.`playIndex` = :index
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("index", $activePlayerIndex);
		$statement->execute();

		$row = $statement->fetch();
		$displayName = htmlspecialchars($row["displayName"]);
		$emailAddress = htmlspecialchars($row["emailAddress"]);
		$statement->closeCursor();

		$message = "You are now the active player in <a href=\"" . colonyGameLink($gameID) . "\">game $gameID</a>.";
		colonyAlertPlayer($displayName, $emailAddress, "Game $gameID", $message);
	}

	function colonyAlertPlayer($displayName, $emailAddress, $subject, $message)
	{
		global $conf;
		$message = "<p>$message</p><p><a href=\"" . $conf["base_url"] . "\">Colony Islands</a></p>";
		# Line breaks in mail headers have to be \r\n, not just \n for Unix/Linux
		$headers =
			"From: Colony Islands <" . $conf["email_from"] . ">\r\n" .
			"Reply-To: " . $conf["email_reply_to"] . "\r\n" .
			"Content-type: text/html"
		;
		mail("\"$displayName\" <$emailAddress>", "[Colony Islands] $subject", $message, $headers, "-r " . $conf["email_reply_to"]);
	}

	/**
         * Returns the result of calling colonyAuthenticateHTTP.
	 */
	function colonyAuthenticate($needsAdmin = FALSE)
	{
		return colonyAuthenticateHTTP($needsAdmin);
	}

	/**
	 * Returns the result of calling colonyAuthenticateEmailPassword with
	 * the cookie data previously sent to the client or sends a request for
	 * authentication if not authenticated.
	 */
	function colonyAuthenticateCookie($needsAdmin = FALSE)
	{
		# For safety while developing, use HTTP authentication in
		# addition
		list($db, $player) = colonyAuthenticateHTTP($needsAdmin);

		$db = colonyConnectDatabase();

		if(array_key_exists("sessionID", $_COOKIE))
		{
			$sessionID = $_COOKIE["sessionID"];

			# Obviously, checking a real session ID should use
			# the database
			if("value" === $sessionID)
				return array($db, $player);
		}

		colonyErrorStart();
?>
<p>You are not logged in.</p>
<form action="login.php" method="POST">
	<div class="form-group">
		<label for="loginEmailAddress">Email address</label>
		<input class="form-control" id="loginEmailAddress" name="emailAddress" type="text">
	</div>
	<div class="form-group">
		<label for="loginPassword">Password</label>
		<input class="form-control" id="loginPassword" name="password" type="password">
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Submit">
	</div>
</form>
<?php
		colonyErrorEnd();
	}

	/**
	 * Returns an array of (database connection, player information) where
	 * "player information" is NULL if not authenticated or an array with
	 * keys ("displayName", "emailAddress", "ID", "isAdmin").
	 */
	function colonyAuthenticateEmailPassword($emailAddress, $password)
	{
		$db = colonyConnectDatabase();

		$statement = $db->prepare("
			SELECT
				displayName,
				ID,
				isAdmin,
				theme,
				passwordHash
			FROM col_players
			WHERE emailAddress = :email
		");
		$statement->bindValue("email", $emailAddress);
		$statement->execute();

		# A login might not return a single row, so break on a match
		$player = NULL;
		while(FALSE !== ($row = $statement->fetch()))
		{
			$dbHash = $row['passwordHash'];
			$playerID = intval($row['ID']);

			# Password hashes starting with a '$' use the PasswordHash
			# library and older hashes created using `md5` need to be
			# upgraded if the login works
			if($dbHash[0] === '$')
			{
				if(!colonyGetPasswordHasher()->CheckPassword($password, $dbHash))
					continue;
			}
			else
			{
				# Don't upgrade hashes that don't match
				if($dbHash != md5($password))
					continue;

				$statement2 = $db->prepare('
					UPDATE col_players
					SET passwordHash = :newHash
					WHERE ID = :player
				');
				$statement2->bindValue('newHash', colonyHashPassword($password));
				$statement2->bindValue('player', $playerID);
				$statement2->execute();
				$statement2->closeCursor();
			}

			$player = array(
				"displayName" => htmlspecialchars($row["displayName"]),
				"emailAddress" => htmlspecialchars($emailAddress),
				"ID" => $playerID,
				"isAdmin" => intval($row["isAdmin"]),
				"theme" => intval($row["theme"])
			);

			break;
		}
		$statement->closeCursor();

		return array($db, $player);
	}

	function colonyAuthenticateGoogleIdentity($needsAdmin = FALSE)
	{
		# For safety while developing, use HTTP authentication in
		# addition
		list($db, $player) = colonyAuthenticateHTTP($needsAdmin);

		session_start();

		global $conf;
		$client_id = $conf["google_client_id"];
		$client_secret = $conf["google_client_secret"];
		$redirect_uri = $conf["base_url"] . "login.php";

		$client = new Google_Client();
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->setScopes("email");

		if(!empty($_SESSION["id_token"]) && isset($_SESSION["id_token"]["id_token"]))
		{
			$client->setAccessToken($_SESSION["id_token"]);
		}
		else
		{
			$_SESSION["redirect"] = $_SERVER["REQUEST_URI"] . $_SERVER["QUERY_STRING"];
			$authUrl = $client->createAuthUrl();
			header("Location: " . filter_var($authUrl, FILTER_SANITIZE_URL));
			exit();
		}

		if ($client->getAccessToken())
		{
			$_SESSION["id_token"] = $client->getAccessToken();
			$token_data = $client->verifyIdToken();

			if (!empty($token_data["email"])) {
				$emailAddress = $token_data["email"];

				$db = colonyConnectDatabase();

				$statement = $db->prepare("
					SELECT
						displayName,
						ID,
						isAdmin,
						theme
					FROM col_players
					WHERE emailAddress = :email
				");
				$statement->bindValue("email", $emailAddress);
				$statement->execute();

				# Break on the first player found
				$player = NULL;
				while(FALSE !== ($row = $statement->fetch()))
				{
					$playerID = intval($row["ID"]);

					$player = array(
						"displayName" => htmlspecialchars($row["displayName"]),
						"emailAddress" => htmlspecialchars($emailAddress),
						"ID" => $playerID,
						"isAdmin" => intval($row["isAdmin"]),
						"theme" => intval($row["theme"])
					);
					break;
				}
				$statement->closeCursor();

				if((NULL !== $player) && (!$needsAdmin || (1 === $player["isAdmin"])))
					return array($db, $player);
			}
		}

		header("HTTP/1.0 401 Unauthorized");
		header("WWW-Authenticate: Basic realm=\"Colony Islands\"");
		colonyError("Invalid username/password. Contact Dan for assistance.");
	}

	/**
	 * Returns the result of calling colonyAuthenticateEmailPassword with
	 * the email address and password provided via HTTP authentication or
	 * sends a request for authentication if not authenticated.
	 */
	function colonyAuthenticateHTTP($needsAdmin = FALSE)
	{
		if(array_key_exists("PHP_AUTH_USER", $_SERVER))
		{
			$emailAddress = $_SERVER["PHP_AUTH_USER"];
			$password = $_SERVER["PHP_AUTH_PW"];
			list ($db, $player) = colonyAuthenticateEmailPassword($emailAddress, $password);
			if((NULL !== $player) && (!$needsAdmin || (1 === $player["isAdmin"])))
				return array($db, $player);
		}

		header("HTTP/1.0 401 Unauthorized");
		header("WWW-Authenticate: Basic realm=\"Colony Islands\"");
		colonyError("Invalid username/password. Contact Dan for assistance.");
	}

	function colonyCheckLongestRoad($gameID, $playerID)
	{
		global $db;

		$statement = $db->prepare("
			SELECT
				`ID`,
				`town1ID`,
				`town2ID`
			FROM `col_roads`
			WHERE
				`gameID` = :game AND
				`playerID` = :player 
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $playerID);
		$statement->execute();

		$roads = array();
		while(FALSE !== ($row = $statement->fetch()))
		{
			$roadID = intval($row["ID"]);
			$town1ID = intval($row["town1ID"]);
			$town2ID = intval($row["town2ID"]);

			if(!array_key_exists($town1ID, $roads))
				$roads[$town1ID] = array();
			$roads[$town1ID][] = array("roadID" => $roadID, "oppositeTownID" => $town2ID);

			if(!array_key_exists($town2ID, $roads))
				$roads[$town2ID] = array();
			$roads[$town2ID][] = array("roadID" => $roadID, "oppositeTownID" => $town1ID);
		}
		$statement->closeCursor();

		# Find all opponent-owned towns on this player's roads to see if any of
		# them might break this player's longest-road
		$opponentTowns = array();
		foreach($roads as $townID => $roadEntries)
		{
			$statement = $db->prepare("
				SELECT '1'
				FROM `col_towns`
				WHERE
					`ID` = :town AND
					`playerID` != 0 AND
					`playerID` != :player
			");
			$statement->bindValue("town", $townID);
			$statement->bindValue("player", $playerID);
			$statement->execute();

			if(FALSE !== $statement->fetch())
				$opponentTowns[$townID] = TRUE;
			$statement->closeCursor();
		}

		$longestLength = 0;
		foreach(array_keys($roads) as $townID)
		{
			$longestLength = max($longestLength, colonyTravelLongestRoad($roads, $opponentTowns, $townID, array(), 0));
		}
		return $longestLength;
	}

	function colonyCheckResource($gameID, $playerID, $type)
	{
		global $db;

		$statement = $db->prepare("
			SELECT COUNT(*) as `count`
			FROM `col_resource_cards`
			WHERE
				`gameID` = :game AND
				`playerID` = :player AND
				`type` = :type
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $playerID);
		$statement->bindValue("type", $type);
		$statement->execute();

		$row = $statement->fetch();
		$amount = intval($row["count"]);
		$statement->closeCursor();

		return $amount;
	}

	function colonyCheckWin($gameID, $playerID)
	{
		global $db;
		$visiblePoints = 0;

		// Add up the number of settlements and cities
		$statement = $db->prepare("
			SELECT SUM(`type`) as `townCount`
			FROM `col_towns`
			WHERE
				`gameID` = :game AND
				`playerID` = :player
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $playerID);
		$statement->execute();

		$row = $statement->fetch();
		$visiblePoints += intval($row["townCount"]);
		$statement->closeCursor();

		// Add bonuses for largest army and longest road
		$statement = $db->prepare("
			SELECT
				`currentTurn`,
				`largestArmyID`,
				`longestRoadID`
			FROM `col_games`
			WHERE `ID` = :game
		");
		$statement->bindValue("game", $gameID);
		$statement->execute();

		$row = $statement->fetch();
		$currentTurn = intval($row["currentTurn"]);
		$largestArmyID = intval($row["largestArmyID"]);
		$longestRoadID = intval($row["longestRoadID"]);
		$statement->closeCursor();

		if($largestArmyID === $playerID)
			$visiblePoints += 2;
		if($longestRoadID === $playerID)
			$visiblePoints += 2;

		// Add victory point cards
		$statement = $db->prepare("
			SELECT
				`ID`,
				`turnUsed`
			FROM `col_development_cards`
			WHERE
				`gameID` = :game AND
				`playerID` = :player AND
				`type` = 'Victory Point'
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $playerID);
		$statement->execute();

		$hiddenPoints = array();
		while(FALSE !== ($row = $statement->fetch()))
		{
			$cardID = intval($row["ID"]);
			$turnUsed = intval($row["turnUsed"]);

			if(0 === $turnUsed)
				array_push($hiddenPoints, $cardID);
			else
				++$visiblePoints;
		}
		$statement->closeCursor();

		# Only reveal hidden victory point cards if the player has won
		if(10 <= $visiblePoints + count($hiddenPoints))
		{
			foreach($hiddenPoints as $victoryPointID)
			{
				$statement = $db->prepare("
					UPDATE `col_development_cards`
					SET `turnUsed` = :turn
					WHERE `ID` = :card
				");
				$statement->bindValue("card", $victoryPointID);
				$statement->bindValue("turn", $currentTurn);
				$statement->execute();
				$statement->closeCursor();

				colonyMessage($gameID, $playerID, "has used a Victory Point development card");
				++$visiblePoints;
			}
		}

		$statement = $db->prepare("
			UPDATE `col_playing`
			SET
				`visiblePoints` = :points
			WHERE
				`gameID` = :game AND
				`playerID` = :player
		");
		$statement->bindValue('points', $visiblePoints);
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $playerID);
		$statement->execute();
		$statement->closeCursor();

		if(10 <= $visiblePoints)
		{
			$statement = $db->prepare("
				UPDATE `col_games`
				SET
					`state` = :state,
					`substate` = :substate
				WHERE `ID` = :game
			");
			$statement->bindValue("game", $gameID);
			$statement->bindValue("state", COMPLETE);
			$statement->bindValue("substate", NO_SUBSTATE);
			$statement->execute();
			$statement->closeCursor();

			colonyMessage($gameID, 0, "Game $gameID has been completed");

			$statement = $db->prepare("
				SELECT
					`col_players`.`displayName`,
					`col_players`.`emailAddress`
				FROM `col_playing`
				LEFT JOIN `col_players` ON `col_playing`.`playerID` = `col_players`.`ID`
				WHERE
					`col_playing`.`gameID` = :game AND
					`col_playing`.`playerID` != :player
			");
			$statement->bindValue("game", $gameID);
			$statement->bindValue("player", $playerID);
			$statement->execute();

			while(FALSE !== ($row = $statement->fetch()))
			{
				$displayName = htmlspecialchars($row["displayName"]);
				$emailAddress = htmlspecialchars($row["emailAddress"]);
				$message = "<a href=\"" . colonyGameLink($gameID) . "\">game $gameID</a> has been completed.";
				colonyAlertPlayer($displayName, $emailAddress, "Game $gameID", $message);
			}
			$statement->closeCursor();

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Returns a PDO object connected to the database
	 */
	function colonyConnectDatabase()
	{
		global $database;
		$db = new PDO($database["pdo_dsn"], $database["username"], $database["password"], array(
			# Need to request a persistent connection when constructing the
			# handle so it actually has an effect
			PDO::ATTR_PERSISTENT => true
		));
		# Can't set this during construction since it's not "driver specific"
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		return $db;
	}

	function colonyCookieSession($value, $expireSeconds)
	{
		global $conf;
		$parts = parse_url($conf["base_url"]);
		setcookie("sessionID", $value, time() + $expireSeconds, $parts["path"], $parts["host"], TRUE, TRUE);
	}

	# This is no longer used, but I'm keeping it around for posterity
	function colonyEncodeJSON($var)
	{
		if(is_array($var))
		{
			$first = TRUE;
			$ret = "{";
			foreach($var as $key => $val)
			{
				if($first)
					$first = FALSE;
				else
					$ret .= ",";
				$ret .= ("\"$key\":" . colonyEncodeJSON($val));
			}
			$ret .= "}";
			return $ret;
		}
		else if(is_bool($var))
		{
			if($var)
				return "true";
			else
				return "false";
		}
		else if(is_float($var) || is_int($var))
			return "$var";
		else if(is_string($var))
			return "\"$var\"";
		else
			colonyError("Unknown type for $var");
	}

	function colonyEnd()
	{
		ob_end_flush();
	}

	# Clear any output built-up in the output buffer and print the error
	function colonyError($message)
	{
		colonyErrorStart();
?>
<p class="error"><?php echo($message);?></p>
<?php
		colonyErrorEnd();
	}

	function colonyErrorEnd()
	{
		colonyHTMLFooter();
		exit(-1);
	}

	function colonyErrorStart()
	{
		global $outputBufferStarted;
		if($outputBufferStarted)
			ob_end_clean();

		colonyHTMLHeader();
	}

	function colonyGameLink($gameID)
	{
		global $conf;
		return $conf["base_url"] . "game.php?gameID=$gameID";
	}

	function colonyGeneratePassword()
	{
		$passwordChars = "abcdefghijkmnopqrstuvwxyz023456789";
		$newPassword = "";
		for($i = 0; $i < 8; ++$i)
			$newPassword = $newPassword . substr($passwordChars, rand() % strlen($passwordChars), 1);
		return $newPassword;
	}

	function colonyGet($key)
	{
		return array_key_exists($key, $_GET);
	}

	function colonyGetPasswordHasher()
	{
		return new PasswordHash(8, FALSE);
	}

	function colonyGetResource($gameID, $playerID, $type, $num)
	{
		global $db;

		# PDO doesn't bind values with limiting very well, so make sure to
		# sanitize it properly
		$num = intval($num);
		$statement = $db->prepare("
			UPDATE `col_resource_cards`
			SET `playerID` = :player
			WHERE
				`gameID` = :game AND
				`playerID` = '0' AND
				`type` = :type
			ORDER BY `ID`
			LIMIT $num
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $playerID);
		$statement->bindValue("type", $type);
		$statement->execute();
		$statement->closeCursor();
	}

	function colonyHashPassword($password)
	{
		return colonyGetPasswordHasher()->HashPassword($password);
	}

	function colonyHTMLEnd()
	{
		colonyHTMLFooter();
		colonyEnd();
	}

	function colonyHTMLFooter()
	{
?>
</div>
</body>
</html>
<?php
	}

	function colonyHTMLHeader($ret = FALSE)
	{
		header("Content-Type: text/html");
		if (isset($ret)) {
			$theme = $ret[1]['theme'];
			global $themes;
			if (isset($themes[$theme])) {
				$theme = 'class="theme-' . $themes[$theme] . '"';
			} else {
				$theme = '';
			}
		} else {
			$theme = '';
		}
?>
<!DOCTYPE html>
<html <?php echo(isset($theme)?$theme:'');?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="favicon.ico" type="image/vnd.microsoft.icon">
<link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="colony.css" type="text/css">
<title>Colony Islands</title>
</head>
<body>
<div class="container">
<?php
	}

	/**
	 * Returns the return value of a call to colonyStart($needsAdmin).
	 */
	function colonyHTMLStart($needsAdmin = FALSE)
	{
		$ret = colonyStart($needsAdmin);
		colonyHTMLHeader($ret);
		return $ret;
	}

	function colonyMessage($gameID, $playerID, $message)
	{
		global $db;

		$statement = $db->prepare("
			INSERT INTO `col_messages`
			SET
				`gameID` = :game,
				`playerID` = :player,
				`time` = NOW(),
				`message` = :message
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("message", $message);
		$statement->bindValue("player", $playerID);
		$statement->execute();
		$statement->closeCursor();
	}

	function colonyPost($key)
	{
		return array_key_exists($key, $_POST);
	}

	function colonySetSubstate($gameID, $substate)
	{
		global $db;

		$statement = $db->prepare("
			UPDATE `col_games`
			SET `substate` = :substate
			WHERE `ID` = :game
		");
		$statement->bindValue("substate", $substate);
		$statement->bindValue("game", $gameID);
		$statement->execute();
		$statement->closeCursor();
	}

	/**
	 * Returns the return value of a call to colonyAuthenticate($needsAdmin).
	 */
	function colonyStart($needsAdmin = FALSE)
	{
		global $outputBufferStarted;
		$outputBufferStarted = ob_start();
		return colonyAuthenticate($needsAdmin);
	}

	function colonyTravelLongestRoad($roads, $opponentTowns, $townID, $traveledRoadIDs, $currentLength)
	{
		if(array_key_exists($townID, $opponentTowns))
			return $currentLength;

		$longestLength = $currentLength;
		foreach($roads[$townID] as $roadEntry)
		{
			$roadID = $roadEntry["roadID"];
			if(!in_array($roadID, $traveledRoadIDs))
			{
				$oppositeTownID = $roadEntry["oppositeTownID"];
				$newTraveledRoadIDs = $traveledRoadIDs;
				$newTraveledRoadIDs[] = $roadID;
				$longestLength = max($longestLength, colonyTravelLongestRoad($roads, $opponentTowns, $oppositeTownID, $newTraveledRoadIDs, $currentLength + 1));
			}
		}
		return $longestLength;
	}

	function colonyUseResource($gameID, $playerID, $type, $num)
	{
		global $db;

		# PDO doesn't bind values with limiting very well, so make sure to
		# sanitize it properly
		$num = intval($num);
		$statement = $db->prepare("
			UPDATE `col_resource_cards`
			SET `playerID` = '0'
			WHERE
				`gameID` = :game AND
				`playerID` = :player AND
				`type` = :type
			ORDER BY `ID`
			LIMIT $num
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $playerID);
		$statement->bindValue("type", $type);
		$statement->execute();
		$statement->closeCursor();
	}
?>
