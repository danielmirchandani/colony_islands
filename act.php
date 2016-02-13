<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate();
	$ID = $loggedIn["ID"];

	$allowedActions = array(
		"acceptTrade",
		"buildCity",
		"buildRoad",
		"buildSettlement",
		"cancel",
		"chooseSubstate",
		"discardResources",
		"domesticTrade",
		"joinGame",
		"maritimeTrade",
		"monopoly",
		"moveRobber",
		"postMessage",
		"rejectTrade",
		"stealResource",
		"useDevelopmentCard",
		"yearOfPlenty",
	);

	if(!colonyPost("action"))
		colonyError("Must specify an action to take");
	$action = $_POST["action"];

	if(!colonyPost("gameID"))
		colonyError("Must specify a gameID to act on");
	$gameID = intval($_POST["gameID"]);

	$matchAction = FALSE;
	foreach($allowedActions as $checkAction)
	{
		if($checkAction === $action)
		{
			$matchAction = TRUE;
			break;
		}
	}

	if(!$matchAction)
		colonyError("$action is not an allowed action");

	$statement = $db->prepare("
		SELECT
			`activePlayerIndex`,
			`cityLimit`,
			`currentTurn`,
			`forcedRoll`,
			`largestArmyAmount`,
			`largestArmyID`,
			`longestRoadAmount`,
			`longestRoadID`,
			`playerLimit`,
			`roadLimit`,
			`robberTileID`,
			`settlementLimit`,
			`state`,
			`substate`,
			`turnPlayerIndex`
		FROM `col_games`
		WHERE `ID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	$row = $statement->fetch();
	$activePlayerIndex = intval($row["activePlayerIndex"]);
	$cityLimit = intval($row["cityLimit"]);
	$currentTurn = intval($row["currentTurn"]);
	$forcedRoll = intval($row["forcedRoll"]);
	$largestArmyAmount = intval($row["largestArmyAmount"]);
	$largestArmyID = intval($row["largestArmyID"]);
	$longestRoadAmount = intval($row["longestRoadAmount"]);
	$longestRoadID = intval($row["longestRoadID"]);
	$playerLimit = intval($row["playerLimit"]);
	$roadLimit = intval($row["roadLimit"]);
	$robberTileID = intval($row["robberTileID"]);
	$settlementLimit = intval($row["settlementLimit"]);
	$state = intval($row["state"]);
	$substate = intval($row["substate"]);
	$turnPlayerIndex = intval($row["turnPlayerIndex"]);
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT `playIndex`
		FROM `col_playing`
		WHERE
			`gameID` = :game AND
			`playerID` = :player
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $ID);
	$statement->execute();

	$row = $statement->fetch();
	if(FALSE === $row)
		colonyError("You can't act on a game you aren't in");

	if(("joinGame" !== $action) && ("postMessage" !== $action))
	{
		$playIndex = intval($row["playIndex"]);
		if($playIndex !== $activePlayerIndex)
			colonyError("You can't act on a game if you aren't the active player");
	}
	$statement->closeCursor();

	include("lib/actions/$action.php");
	header("Location: game.php?gameID=$gameID");
?>
