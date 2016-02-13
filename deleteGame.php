<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate(TRUE);

	if(!colonyPost("gameID"))
		colonyError("Must provide gameID for game deletion");
	$gameID = intval($_POST["gameID"]);

	$statement = $db->prepare("
		UPDATE col_games
		SET isHidden = 1
		WHERE ID = :game
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();
	if(1 !== $statement->rowCount())
		colonyError("Could not insert game row into deleted-games table");
	$statement->closeCursor();

	header("Location: index.php");
?>
