<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyHTMLStart();

	$allowedStates = array(
		WAITING_FOR_PLAYERS => "waiting",
		FIRST_CHOICE => "first",
		SECOND_CHOICE => "second",
		BEFORE_ROLL => "before",
		AFTER_ROLL => "after",
		COMPLETE => "complete",
	);
	$allowedSubstates = array(
		NO_SUBSTATE => "noSubstate",
		BUILD_CITY => "buildCity",
		BUILD_SETTLEMENT => "buildSettlement",
		BUILD_ROAD => "buildRoad",
		CHOOSE_ACTION => "chooseAction",
		DISCARD_RESOURCES => "discardResources",
		DOMESTIC_TRADE => "domesticTrade",
		MARITIME_TRADE => "maritimeTrade",
		MOVE_ROBBER => "moveRobber",
		STEAL_RESOURCE => "stealResource",
		USE_DEVELOPMENT_CARD => "useDevelopmentCard",
		ROAD_BUILDING_1 => "roadBuilding1",
		ROAD_BUILDING_2 => "roadBuilding2",
		CELEBRATE_YEAR_OF_PLENTY => "yearOfPlenty",
		MONOPOLIZE => "monopoly",
		REVIEW_TRADE => "reviewTrade",
	);

	if(!colonyGet("gameID"))
		colonyError("Must provide a gameID");
 	$gameID = intval($_GET["gameID"]);

	$statement = $db->prepare("
		SELECT
			`activePlayerIndex`,
			`cityLimit`,
			`largestArmyAmount`,
			`largestArmyID`,
			`longestRoadAmount`,
			`longestRoadID`,
			`roadLimit`,
			`robberTileID`,
			`settlementLimit`,
			`state`,
			`substate`,
			`turnPlayerIndex`
		FROM `col_games`
		WHERE
			`ID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("Game $gameID doesn't exist");

	$activePlayerIndex = intval($row["activePlayerIndex"]);
	$cityLimit = intval($row["cityLimit"]);
	$largestArmyAmount = intval($row["largestArmyAmount"]);
	$largestArmyID = intval($row["largestArmyID"]);
	$longestRoadAmount = intval($row["longestRoadAmount"]);
	$longestRoadID = intval($row["longestRoadID"]);
	$roadLimit = intval($row["roadLimit"]);
	$robberTileID = intval($row["robberTileID"]);
	$settlementLimit = intval($row["settlementLimit"]);
	$state = intval($row["state"]);
	$substate = intval($row["substate"]);
	$turnPlayerIndex = intval($row["turnPlayerIndex"]);
	$statement->closeCursor();

	$activePlayer = NULL;
	$me = NULL;
	$playerCount = 0;
	$players = array();
	$turnPlayer = NULL;

	$statement = $db->prepare("
		SELECT
			(
				SELECT COUNT(*)
				FROM `col_towns`
				WHERE
					`gameID` = col_playing.gameID AND
					`playerID` = `col_playing`.`playerID` AND
					`type` = '2'
			) AS `cityCount`,
			`colorID`,
			`col_players`.`displayName`,
			`col_players`.`ID`,
			`isJoined`,
			`playIndex`,
			(
				SELECT COUNT(*)
				FROM `col_resource_cards`
				WHERE
					`gameID` = col_playing.gameID AND
					`playerID` = `col_playing`.`playerID`
			) AS `resourceCardCount`,
			(
				SELECT COUNT(*)
				FROM `col_roads`
				WHERE
					`gameID` = col_playing.gameID AND
					`playerID` = `col_playing`.`playerID`
			) AS `roadCount`,
			(
				SELECT COUNT(*)
				FROM `col_towns`
				WHERE
					`gameID` = col_playing.gameID AND
					`playerID` = `col_playing`.`playerID` AND
					`type` = '1'
			) AS `settlementCount`,
			(
				SELECT COUNT(*)
				FROM `col_development_cards`
				WHERE
					`gameID` = col_playing.gameID AND
					`playerID` = `col_playing`.`playerID` AND
					`turnUsed` = '0'
			) AS `unusedDevelopmentCardCount`,
			`visiblePoints`
		FROM `col_playing`
		LEFT JOIN `col_players` ON `col_playing`.`playerID` = `col_players`.`ID`
		WHERE `gameID` = :game
		ORDER BY `playIndex`
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		++$playerCount;

		array_push($players, array(
			"cityCount" => intval($row["cityCount"]),
			"colorID" => intval($row["colorID"]),
			"displayName" => htmlspecialchars($row["displayName"]),
			"ID" => intval($row["ID"]),
			"isJoined" => intval($row["isJoined"]),
			"playIndex" => intval($row["playIndex"]),
			"resourceCardCount" => intval($row["resourceCardCount"]),
			"roadCount" => intval($row["roadCount"]),
			"settlementCount" => intval($row["settlementCount"]),
			"unusedDevelopmentCardCount" => intval($row["unusedDevelopmentCardCount"]),
			"visiblePoints" => intval($row["visiblePoints"])
		));
	}
	$statement->closeCursor();

	foreach($players as $playIndex => $player)
	{
		$usedCards = $db->prepare("
			SELECT `type`
			FROM col_development_cards
			WHERE
				`gameID` = :game AND
				`playerID` = :player AND
				`turnUsed` != '0'
		");
		$usedCards->bindValue("game", $gameID);
		$usedCards->bindValue("player", $player["ID"]);
		$usedCards->execute();

		$usedDevelopmentCardCount = 0;
		$usedDevelopmentCards = array();
		while(FALSE !== ($row = $usedCards->fetch()))
		{
			$type = htmlspecialchars($row["type"]);
			++$usedDevelopmentCardCount;
			array_push($usedDevelopmentCards, $type);
		}
		$usedCards->closeCursor();

		$players[$playIndex]["usedDevelopmentCardCount"] = $usedDevelopmentCardCount;
		$players[$playIndex]["usedDevelopmentCards"] = $usedDevelopmentCards;
		if($playIndex === $activePlayerIndex)
			$activePlayer = $players[$playIndex];
		if($player["ID"] === $loggedIn["ID"])
			$me = $players[$playIndex];
		if($playIndex === $turnPlayerIndex)
			$turnPlayer = $players[$playIndex];
	}

	$statement = $db->prepare("
		SELECT
			col_players.displayName,
			col_messages.ID,
			col_messages.message,
			col_messages.time,
			col_messages.isSay
		FROM col_messages
		LEFT JOIN col_players ON col_messages.playerID = col_players.ID
		WHERE gameID = :game
		ORDER BY col_messages.time DESC, col_messages.ID DESC
		LIMIT 25
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	$lastMessageID = 0;
	$messages = array();
	while(FALSE !== ($row = $statement->fetch()))
	{
		$messageID = intval($row["ID"]);
		if($lastMessageID < $messageID)
			$lastMessageID = $messageID;

		array_push($messages, array(
			"playerName" => htmlspecialchars($row["displayName"]),
			"ID" => $messageID,
			"text" => htmlspecialchars($row["message"]),
			"time" => htmlspecialchars($row["time"]),
			"isSay" => intval($row["isSay"]),
		));
	}
	$statement->closeCursor();

	$lastRolled = 0;
	foreach($messages as $message)
	{
		 if(1 === preg_match("/rolled a (\d+)/", $message["text"], $matches))
		 {
		 	$lastRolled = $matches[1];
		 	break;
		 }
	}
?>
<div class="row">
<div class="col-xs-12">
<ol class="breadcrumb">
	<li><a href="index.php">Home</a></li>
	<li class="active">Game <?php echo($gameID);?></li>
</ol>
</div>
</div>
<div class="row">
<div class="col-xs-12 col-md-6 col-md-push-6">
	<div id="board"></div>
</div>
<div class="col-xs-12 col-md-6 col-md-pull-6">
<?php
	include("lib/game/playerStatus.php");
	include("lib/game/gameStatus.php");
?>
</div>
</div>
<div class="row">
<div class="col-xs-12">
<?php
	include("lib/game/messages.php");
?>
</div>
</div>
<?php
	if(1 === $loggedIn["isAdmin"])
	{
?>
<div class="row">
<div class="col-xs-12">
<?php
		include("lib/game/adminActions.php");
?>
</div>
</div>
<?php
	}
?>
<script>
var dojoConfig = {
	async: true,
	packages: [{
		name: "colony",
		location: location.pathname.replace(/\/[^\/]+$/, '')
	}]
};
</script>
<script src="https://ajax.googleapis.com/ajax/libs/dojo/1.10.3/dojo/dojo.js"></script>
<script>
/*global require: false*/
/*jslint white: true */
// How to draw the board
require([
	'colony/board',
	'dojo/dom',
	'dojo/on',
	'dojo/request',
	'dojo/domReady!'
], function (board, dom, on, request) {
	'use strict';

	// Append the last message ID to prevent IE from forcing a 304 response
	// because it thinks the content never changes
	request.get('board.php?gameID=<?php echo($gameID);?>&lastMessageID=<?php echo($lastMessageID);?>', {
		handleAs: "json"
	}).then(function (data) {
		var node, surface;
		data.lastRolled = <?php echo($lastRolled);?>;
		node = dom.byId('board');
		surface = board.create(node, data);
		on(window, 'resize', function () {
			surface.destroy();
			surface = board.create(node, data);
		});
	}, function (error) {
		alert('Error retrieving board data for game ' + <?php echo($gameID);?>);
	});
});

// Toggle player info
require([
	'dojo/query',
	'dojo/NodeList-dom', // Provides query().toggleClass()
	'dojo/domReady!'
], function (query) {
	'use strict';

	query('.togglePlayerInfo').on('click', function (e) {
		var playerIndex = this.getAttribute('data-player-index');
		query('.togglePlayerInfo[data-player-index="' + playerIndex + '"]').toggleClass('dropup');
		query('.playerInfo[data-player-index="' + playerIndex + '"]').toggleClass('hidden');
	});
});
</script>
<?php
	colonyHTMLEnd();
?>
