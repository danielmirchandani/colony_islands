<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to celebrate a year of plenty");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
		$statement = $db->prepare("
			SELECT COUNT(*) as `count` 
			FROM col_resource_cards
			WHERE
				`gameID` = '$gameID' AND
				`playerID` = '0'
		");
		$statement->bindValue("game", $gameID);
		$statement->execute();

		$row = $statement->fetch();
		$resourcesLeft = intval($row["count"]);
		$statement->closeCursor();

		$resourceLimit = min(2, $resourcesLeft);
?>
<p>Choose <?php echo($resourceLimit);?> resource cards to receive:</p>
<form method="POST" action="act.php">
	<input name="action" type="hidden" value="yearOfPlenty">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<div class="form-group">
		<label for="yearOfPlentyBrick">Brick</label>
		<input class="form-control" id="yearOfPlentyBrick" name="Brick" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="yearOfPlentyGrain">Grain</label>
		<input class="form-control" id="yearOfPlentyGrain" name="Grain" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="yearOfPlentyLumber">Lumber</label>
		<input class="form-control" id="yearOfPlentyLumber" name="Lumber" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="yearOfPlentyOre">Ore</label>
		<input class="form-control" id="yearOfPlentyOre" name="Ore" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="yearOfPlentyWool">Wool</label>
		<input class="form-control" id="yearOfPlentyWool" name="Wool" type="text" value="0">
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Celebrate Year of Plenty">
	</div>
</form>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is celebrating a year of plenty.</p>
<?php
	}
?>
