<h1>After roll actions</h1>
<?php
	if(null !== $me)
	{
		$statement = $db->prepare("
			SELECT `type`
			FROM `col_resource_cards`
			WHERE
				`gameID` = :game AND
				`playerID` = :player
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $loggedIn["ID"]);
		$statement->execute();

		$resources = array();
		while(FALSE !== ($row = $statement->fetch()))
		{
			$type = htmlspecialchars($row["type"]);
			if(!array_key_exists($type, $resources))
				$resources[$type] = 0;
			$resources[$type] += 1;
		}
		$statement->closeCursor();
?>
<p>You have the following resource cards:</p>
<ul>
<?php
		foreach($resources as $type => $count)
		{
			$type = preg_replace($CARD_PATTERNS, $CARD_IMAGE_HTML, $type);
?>
	<li><?php echo($count);?> <?php echo($type);?></li>
<?php
		}
?>
</ul>
<p>You have the following unused development cards:</p>
<ul>
<?php
		$statement = $db->prepare("
			SELECT `type`
			FROM `col_development_cards`
			WHERE
				`gameID` = :game AND
				`playerID` = :player AND
				`turnUsed` = '0'
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $loggedIn["ID"]);
		$statement->execute();

		while(FALSE !== ($row = $statement->fetch()))
		{
			$type = htmlspecialchars($row["type"]);
			$type = preg_replace($CARD_PATTERNS, $CARD_IMAGE_HTML, $type);
?>
	<li><?php echo($type);?></li>
<?php
		}
		$statement->closeCursor();
?>
</ul>
<?php
	}

	$name = ($turnPlayerIndex === $me["playIndex"] ? "your" : $turnPlayer["displayName"] . "'s");
?>
<p>It is <?php echo($name);?> turn.</p>
