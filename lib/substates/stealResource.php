<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to steal a resource card");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
?>
<form method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="stealResource">
	<div class="form-group">
		<label for="stealResourcePlayerID">Steal from</label>
		<select class="form-control" id="stealResourcePlayerID" name="playerID">
<?php
		$statement = $db->prepare("
			SELECT DISTINCT
				`playerID`,
				`displayName`
			FROM `col_towns`
			LEFT JOIN col_players ON col_towns.`playerID` = col_players.`ID`
			WHERE
				`gameID` = :game AND
				(
					`tile1ID` = :robber OR
					`tile2ID` = :robber OR
					`tile3ID` = :robber
				) AND
				`playerID` != '0' AND
				`playerID` != :player
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("robber", $robberTileID);
		$statement->bindValue("player", $loggedIn["ID"]);
		$statement->execute();

		while(FALSE !== ($row = $statement->fetch()))
		{
			$playerID = intval($row["playerID"]);
			$displayName = htmlspecialchars($row["displayName"]);
?>
			<option value="<?php echo($playerID);?>"><?php echo($displayName);?></option>
<?php
		}
		$statement->closeCursor();
?>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Steal Resource">
	</div>
</form>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is stealing a resource card.</p>
<?php
	}
?>
