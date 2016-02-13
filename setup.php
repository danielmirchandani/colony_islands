<?php
	require("lib/common.php");

	$db = colonyConnectDatabase();

	# This is MySQL specific - use InnoDB tables by default so we get
	# transactions
	$db->query("SET storage_engine=INNODB")->closeCursor();



	# Layer 1 tables: tables which do not depend on any others

	$db->query("
		CREATE TABLE IF NOT EXISTS col_games
		(
			ID                INT      NOT NULL auto_increment,
			playerLimit       INT      NOT NULL,
			width             DOUBLE   NOT NULL,
			height            DOUBLE   NOT NULL,
			robberTileID      INT      NOT NULL,
			state             SMALLINT NOT NULL DEFAULT 0,
			turnPlayerIndex   SMALLINT NOT NULL DEFAULT 0,
			substate          SMALLINT NOT NULL DEFAULT 0,
			activePlayerIndex SMALLINT NOT NULL DEFAULT 0,
			forcedRoll        SMALLINT NOT NULL DEFAULT 0,
			currentTurn       INT      NOT NULL DEFAULT 0,
			roadLimit         SMALLINT NOT NULL,
			settlementLimit   SMALLINT NOT NULL,
			cityLimit         SMALLINT NOT NULL,
			longestRoadID     INT      NOT NULL DEFAULT 0,
			longestRoadAmount INT      NOT NULL DEFAULT 0,
			largestArmyID     INT      NOT NULL DEFAULT 0,
			largestArmyAmount INT      NOT NULL DEFAULT 0,
			isHidden          SMALLINT NOT NULL DEFAULT 0,
			PRIMARY KEY (ID)
		)
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_info
		(
			databaseSchemaVersion INT       NOT NULL auto_increment,
			databaseSchemaTime    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (databaseSchemaVersion)
		)
	")->closeCursor();

	$db->query("INSERT IGNORE INTO col_info SET databaseSchemaVersion = 1")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_players
		(
			ID           INT          NOT NULL auto_increment,
			displayName  VARCHAR(64)  NOT NULL,
			emailAddress VARCHAR(256) NOT NULL,
			passwordHash VARCHAR(256) NOT NULL,
			isAdmin      SMALLINT     NOT NULL DEFAULT FALSE,
			theme        SMALLINT     NOT NULL DEFAULT 0,
			PRIMARY KEY (ID)
		)
	")->closeCursor();

	$db->query("
		INSERT IGNORE INTO col_players SET
			ID = 1,
			displayName = \"Admin\",
			emailAddress = \"admin@example.com\",
			passwordHash = \"" . md5("password") . "\",
			isAdmin = 1
	")->closeCursor();



	# Layer 2 tables: tables which depend on layer 1 tables

	$db->query("
		CREATE TABLE IF NOT EXISTS col_development_cards
		(
			ID         INT         NOT NULL auto_increment,
			gameID     INT         NOT NULL,
			type       VARCHAR(32) NOT NULL,
			playerID   INT         NOT NULL DEFAULT 0,
			turnBought INT         NOT NULL DEFAULT 0,
			turnUsed   INT         NOT NULL DEFAULT 0,
			PRIMARY KEY (ID),
			INDEX (gameID),
			INDEX (playerID),
			CONSTRAINT FOREIGN KEY (gameID)   REFERENCES col_games (ID)
		 )
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_messages
		(
			ID       INT           NOT NULL auto_increment,
			gameID   INT           NOT NULL,
			playerID INT           NOT NULL,
			time     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			message  VARCHAR(1024) NOT NULL,
			isSay    SMALLINT      NOT NULL DEFAULT 0,
			PRIMARY KEY (ID),
			INDEX (gameID),
			INDEX (playerID),
			CONSTRAINT FOREIGN KEY (gameID)   REFERENCES col_games (ID)
		)
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_playing
		(
			ID            INT      NOT NULL auto_increment,
			gameID        INT      NOT NULL,
			playIndex     INT      NOT NULL DEFAULT 0,
			playerID      INT      NOT NULL,
			colorID       INT      NOT NULL DEFAULT 0,
			visiblePoints SMALLINT NOT NULL DEFAULT 0,
			isJoined      SMALLINT NOT NULL DEFAULT 0,
			PRIMARY KEY (ID),
			INDEX (gameID),
			INDEX (playerID),
			CONSTRAINT FOREIGN KEY (gameID)   REFERENCES col_games (ID),
			CONSTRAINT FOREIGN KEY (playerID) REFERENCES col_players (ID)
		)
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_resource_cards
		(
			ID       INT        NOT NULL auto_increment,
			gameID   INT        NOT NULL,
			type     VARCHAR(8) NOT NULL,
			playerID INT        NOT NULL DEFAULT 0,
			PRIMARY KEY (ID),
			INDEX (gameID),
			INDEX (playerID),
			CONSTRAINT FOREIGN KEY (gameID) REFERENCES col_games (ID)
		)
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_tiles
		(
			ID       INT        NOT NULL auto_increment,
			gameID   INT        NOT NULL,
			type     VARCHAR(8) NOT NULL,
			diceRoll SMALLINT   NOT NULL,
			x        DOUBLE     NOT NULL,
			y        DOUBLE     NOT NULL,
			PRIMARY KEY (ID),
			INDEX (gameID),
			CONSTRAINT FOREIGN KEY (gameID) REFERENCES col_games (ID)
		)
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_trades
		(
			ID        INT      NOT NULL auto_increment,
			gameID    INT      NOT NULL,
			player1ID INT      NOT NULL,
			player2ID INT      NOT NULL,
			status    SMALLINT NOT NULL DEFAULT 0,
			PRIMARY KEY (ID),
			INDEX (gameID),
			INDEX (player1ID),
			INDEX (player2ID),
			CONSTRAINT FOREIGN KEY (gameID)    REFERENCES col_games (ID),
			CONSTRAINT FOREIGN KEY (player1ID) REFERENCES col_players (ID),
			CONSTRAINT FOREIGN KEY (player2ID) REFERENCES col_players (ID)
		)
	")->closeCursor();



	# Layer 3 tables: tables which depend on layer 2 tables

	$db->query("
		CREATE TABLE IF NOT EXISTS col_towns
		(
			ID       INT      NOT NULL auto_increment,
			gameID   INT      NOT NULL,
			tile1ID  INT      NOT NULL,
			tile2ID  INT      NOT NULL,
			tile3ID  INT      NOT NULL,
			type     SMALLINT NOT NULL DEFAULT 0,
			playerID INT      NOT NULL DEFAULT 0,
			PRIMARY KEY (ID),
			INDEX (gameID),
			INDEX (tile1ID),
			INDEX (tile2ID),
			INDEX (tile3ID),
			CONSTRAINT FOREIGN KEY (gameID) REFERENCES col_games (ID),
			CONSTRAINT FOREIGN KEY (tile1ID) REFERENCES col_tiles (ID),
			CONSTRAINT FOREIGN KEY (tile2ID) REFERENCES col_tiles (ID),
			CONSTRAINT FOREIGN KEY (tile3ID) REFERENCES col_tiles (ID)
		)
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_trade_cards
		(
			ID      INT        NOT NULL auto_increment,
			tradeID INT        NOT NULL,
			type    VARCHAR(8) NOT NULL,
			amount  INT        NOT NULL,
			PRIMARY KEY (ID),
			CONSTRAINT FOREIGN KEY (tradeID) REFERENCES col_trades (ID)
		)
	")->closeCursor();



	# Layer 4 tables: tables which depend on layer 3 tables

	$db->query("
		CREATE TABLE IF NOT EXISTS col_ports
		(
			ID       INT        NOT NULL auto_increment,
			gameID   INT        NOT NULL,
			town1ID  INT        NOT NULL,
			town2ID  INT        NOT NULL,
			tileID   INT        NOT NULL,
			amount   SMALLINT   NOT NULL,
			resource VARCHAR(8) NOT NULL,
			PRIMARY KEY (ID),
			INDEX (gameID),
			KEY (town1ID),
			KEY (town2ID),
			KEY (tileID),
			CONSTRAINT FOREIGN KEY (gameID)  REFERENCES col_games (ID),
			CONSTRAINT FOREIGN KEY (town1ID) REFERENCES col_towns (ID),
			CONSTRAINT FOREIGN KEY (town2ID) REFERENCES col_towns (ID),
			CONSTRAINT FOREIGN KEY (tileID)  REFERENCES col_tiles (ID)
		)
	")->closeCursor();

	$db->query("
		CREATE TABLE IF NOT EXISTS col_roads
		(
			ID       INT NOT NULL auto_increment,
			gameID   INT NOT NULL,
			town1ID  INT NOT NULL,
			town2ID  INT NOT NULL,
			playerID INT NOT NULL,
			PRIMARY KEY (ID),
			KEY (gameID),
			KEY (town1ID),
			KEY (town2ID),
			CONSTRAINT FOREIGN KEY (gameID)  REFERENCES col_games (ID),
			CONSTRAINT FOREIGN KEY (town1ID) REFERENCES col_towns (ID),
			CONSTRAINT FOREIGN KEY (town2ID) REFERENCES col_towns (ID)
		)
	")->closeCursor();



	$statement = $db->query("SELECT MAX(databaseSchemaVersion) AS version FROM col_info");
	$row = $statement->fetch();
	$version = intval($row["version"]);
	$statement->closeCursor();

	# Change the condition to "1 === $version" for development
	if(false)
	{
		# Version 2 adds a session identifier for each user to use
		# instead of sending a username/password with every request


		$db->query('
			ALTER TABLE col_players
			ADD session VARCHAR(64) AFTER theme
		')->closeCursor();


		$db->query("INSERT INTO col_info SET databaseSchemaVersion = 2")->closeCursor();
		$version = 2;
	}

	# Change the condition to "2 === $version" for development
	if(false)
	{
		# Version 3 upgrades development card and resource card types
		# from strings to numbers


		$db->query('
			ALTER TABLE col_development_cards
			ADD typeID INT NOT NULL AFTER type
		')->closeCursor();

		$statement = $db->prepare('
			UPDATE col_development_cards
			SET typeID = :cardID
			WHERE type = :cardName
		');
		foreach($DEVELOPMENT_CARDS as $cardID => $cardName)
		{
			$statement->bindValue('cardID', $cardID);
			$statement->bindValue('cardName', $cardName);
			$statement->execute();
			$statement->closeCursor();
		}

		$db->query('
			ALTER TABLE col_development_cards
			DROP type
		')->closeCursor();


		$db->query('
			ALTER TABLE col_resource_cards
			ADD typeID INT NOT NULL AFTER type
		')->closeCursor();

		$statement = $db->prepare('
			UPDATE col_resource_cards
			SET typeID = :cardID
			WHERE type = :cardName
		');
		foreach($RESOURCE_CARDS as $cardID => $cardName)
		{
			$statement->bindValue('cardID', $cardID);
			$statement->bindValue('cardName', $cardName);
			$statement->execute();
			$statement->closeCursor();
		}

		$db->query('
			ALTER TABLE col_resource_cards
			DROP type
		')->closeCursor();


		$db->query("INSERT INTO col_info SET databaseSchemaVersion = 3")->closeCursor();
		$version = 3;
	}

	header("Location: index.php");
?>
