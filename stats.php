<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyHTMLStart();

	if(!colonyGet("playerID"))
		colonyError("Must provide a playerID");
	$playerID = intval($_GET["playerID"]);

	$statement = $db->prepare("
		SELECT displayName
		FROM col_players
		WHERE ID = :playerID
	");
	$statement->bindValue("playerID", $playerID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$displayName = htmlspecialchars($row["displayName"]);
	}
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT COUNT(*) AS gamesPlaying
		FROM col_games JOIN col_playing ON col_games.ID = col_playing.gameID
		WHERE
			col_games.state != :complete AND
			col_games.isHidden = 0 AND
			col_playing.playerID = :playerID
	");
	$statement->bindValue("complete", COMPLETE);
	$statement->bindValue("playerID", $playerID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$gamesPlaying = intval($row["gamesPlaying"]);
	}
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT
			COUNT(*) AS gamesPlayed,
			col_games.playerLimit AS playerLimit
		FROM col_games JOIN col_playing ON col_games.ID = col_playing.gameID
		WHERE
			col_games.state = :complete AND
			col_games.isHidden = 0 AND
			col_playing.playerID = :playerID
		GROUP BY col_games.playerLimit
	");
	$statement->bindValue("complete", COMPLETE);
	$statement->bindValue("playerID", $playerID);
	$statement->execute();

	$gamesPlayedPerPlayerLimit = [];
	$totalGamesPlayed = 0;
	while(FALSE !== ($row = $statement->fetch()))
	{
		$gamesPlayed = intval($row["gamesPlayed"]);
		$playerLimit = intval($row["playerLimit"]);
		$gamesPlayedPerPlayerLimit[$playerLimit] = $gamesPlayed;
		$totalGamesPlayed += $gamesPlayed;
	}
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT
			COUNT(*) AS gamesWon,
			col_games.playerLimit AS playerLimit
		FROM col_games JOIN col_playing ON
			col_games.ID = col_playing.gameID AND
			col_games.activePlayerIndex = col_playing.playIndex
		WHERE
			col_games.state = :complete AND
			col_games.isHidden = 0 AND
			col_playing.playerID = :playerID
		GROUP BY col_games.playerLimit
	");
	$statement->bindValue("complete", COMPLETE);
	$statement->bindValue("playerID", $playerID);
	$statement->execute();

	$gamesWonPerPlayerLimit = [];
	while(FALSE !== ($row = $statement->fetch()))
	{
		$gamesWon = intval($row["gamesWon"]);
		$playerLimit = intval($row["playerLimit"]);
		$gamesWonPerPlayerLimit[$playerLimit] = $gamesWon;
	}
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT MAX(longestRoadAmount) AS longestLongestRoad
		FROM col_games
		WHERE longestRoadID = :playerID
	");
	$statement->bindValue("playerID", $playerID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$longestLongestRoad = intval($row["longestLongestRoad"]);
	}
	$statement->closeCursor();

	$statement = $db->prepare("
		SELECT MAX(largestArmyAmount) AS largestLargestArmy
		FROM col_games
		WHERE largestArmyID = :playerID
	");
	$statement->bindValue("playerID", $playerID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$largestLargestArmy = intval($row["largestLargestArmy"]);
	}
	$statement->closeCursor();
?>
<div class="row">
<div class="col-xs-12">
<h1>Statistics</h1>
<p><?php echo($displayName);?></p>
<ul>
<li>Games playing: <?php echo($gamesPlaying);?></li>
<li>Total games played: <?php echo($totalGamesPlayed);?></li>
<?php
	foreach($gamesWonPerPlayerLimit as $playerLimit => $gamesWon)
	{
		$numerator = $gamesWonPerPlayerLimit[$playerLimit];
		$denominator = $gamesPlayedPerPlayerLimit[$playerLimit];
?>
<li><?php echo($playerLimit);?>-player games won: <?php echo($numerator);?>/<?php echo($denominator);?> (<?php echo(round(100 * $numerator / $denominator, 2));?>%)</li>
<?php
	}
?>
<li>Longest longest road: <?php echo($longestLongestRoad);?></li>
<li>Largest largest army: <?php echo($largestLargestArmy);?></li>
</ul>
</div>
</div>
<?php
	colonyHTMLEnd();
?>
