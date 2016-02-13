<h1>Waiting for players</h1>
<?php
	if((NULL !== $me) && (0 === $me["isJoined"]))
	{
?>
<p>Choose a color to join the game:</p>
<form method="POST" action="act.php">
	<input name="action" type="hidden" value="joinGame">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
<?php
		$statement = $db->prepare("
			SELECT colorID
			FROM col_playing
			WHERE gameID = :game
		");
		$statement->bindValue("game", $gameID);
		$statement->execute();

		# Color 0 only exists for players who haven't joined the game yet
		$ignore = array(0);
		while(FALSE !== ($row = $statement->fetch()))
		{
			$colorID = intval($row["colorID"]);
			array_push($ignore, $colorID);
		}
		$statement->closeCursor();

		foreach($colors as $colorID => $colorName)
		{
			if(!in_array($colorID, $ignore))
			{
?>
	<div class="playerColor<?php echo($colorID);?> radio">
		<label>
			<input name="colorID" type="radio" value="<?php echo($colorID);?>">
			<?php echo($colorName);?>
		</label>
	</div>
<?php
			}
		}
?>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Join this game">
	</div>
</form>
<?php
	}
?>
