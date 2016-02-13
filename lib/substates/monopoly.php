<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to monopolize a resource");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
?>
<p>Choose which resource to monopoly:</p>
<form id="selectForm" method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="monopoly">
	<div class="form-group">
		<label for="monopolyResource">Resource</label>
		<select class="form-control" id="monopolyResource" name="resource">
			<option value="Brick">Brick</option>
			<option value="Grain">Grain</option>
			<option value="Lumber">Lumber</option>
			<option value="Ore">Ore</option>
			<option value="Wool">Wool</option>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Monopolize Resource">
	</div>
</form>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is choosing which resource to monopolize.</p>
<?php
	}
?>
