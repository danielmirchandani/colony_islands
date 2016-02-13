<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyHTMLStart();

	if(!colonyGet("gameID"))
		colonyError("Must provide a gameID");
 	$gameID = intval($_GET["gameID"]);
?>
<div class="row">
<div class="col-xs-12">
<h1>Messages</h1>
<ul>
<?php
	$statement = $db->prepare("
		SELECT
			`col_players`.`displayName`,
			`col_messages`.`message`,
			`col_messages`.`time`,
			`col_messages`.`isSay`
		FROM `col_messages`
		LEFT JOIN `col_players` ON `col_messages`.`playerID` = `col_players`.`ID`
		WHERE `gameID` = :game
		ORDER BY `col_messages`.`time` DESC, `col_messages`.`ID` DESC
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$playerName = htmlspecialchars($row["displayName"]);
		$message = htmlspecialchars($row["message"]);
		$time = htmlspecialchars($row["time"]);
		$isSay = intval($row["isSay"]);

		$message = preg_replace($CARD_PATTERNS, $CARD_IMAGE_HTML, $message);

		if(1 === $isSay)
		{
?>
	<li><?php echo($time);?> - <?php echo($playerName);?>: <strong><?php echo($message);?></strong></li>
<?php
		}
		else
		{
?>
	<li><?php echo($time);?> - <?php echo($playerName);?> <?php echo($message);?></li>
<?php
		}
	}
	$statement->closeCursor();
?>
</ul>
</div>
</div>
<?php
	colonyHTMLEnd();
?>
