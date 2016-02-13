<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate(TRUE);

	if(!colonyPost("gameID") || !colonyPost("roll"))
		colonyError("Must provide gameID and roll to force a roll");
	$gameID = intval($_POST["gameID"]);
	$roll = intval($_POST["roll"]);

	if(($roll < 2) || (12 < $roll))
		colonyError("A forced roll must be between 2 and 12");

	$statement = $db->prepare("
		UPDATE `col_games`
		SET `forcedRoll` = :roll
		WHERE `ID` = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("roll", $roll);
	$statement->execute();
	if(1 !== $statement->rowCount())
		colonyError("Could not update games table with a forced roll");
	$statement->closeCursor();

	header("Location: game.php?gameID=$gameID");
?>
