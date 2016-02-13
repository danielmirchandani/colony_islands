<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to discard resources");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
		$statement = $db->prepare("
			SELECT COUNT(*) as `count` 
			FROM `col_resource_cards`
			WHERE
				`gameID` = :game AND
				`playerID` = :player
		");
		$statement->bindValue("game", $gameID);
		$statement->bindValue("player", $loggedIn["ID"]);
		$statement->execute();

		$row = $statement->fetch();
		$resourceCount = intval($row["count"]);
		$discardAmount = intval(floor($resourceCount / 2));
		$statement->closeCursor();
?>
<p>Choose <?php echo($discardAmount);?> resource cards to discard:</p>
<form method="POST" action="act.php">
	<input name="action" type="hidden" value="discardResources">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<div class="form-group">
		<label for="discardResourcesBrick">Brick</label>
		<input class="form-control" id="discardResourcesBrick" name="Brick" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="discardResourcesGrain">Grain</label>
		<input class="form-control" id="discardResourcesGrain" name="Grain" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="discardResourcesLumber">Lumber</label>
		<input class="form-control" id="discardResourcesLumber" name="Lumber" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="discardResourcesOre">Ore</label>
		<input class="form-control" id="discardResourcesOre" name="Ore" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="discardResourcesWool">Wool</label>
		<input class="form-control" id="discardResourcesWool" name="Wool" type="text" value="0">
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Discard Resources">
	</div>
</form>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is choosing which resource cards to discard.</p>
<?php
	}
?>
