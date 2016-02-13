<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID is not in state BEFORE_ROLL or AFTER_ROLL for substate $substate");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
		if(BEFORE_ROLL === $state)
		{
?>
<p>Choose what to do:</p>
<form method="POST" action="act.php">
	<input name="action" type="hidden" value="chooseSubstate">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<div class="form-group">
		<label for="chooseSubstateSubstate">Action</label>
		<select class="form-control" id="chooseSubstateSubstate" name="substate">
			<option value="useDevelopmentCard">Use a development card</option>
			<option value="rollDice">Roll the dice</option>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Choose Action">
	</div>
</form>
<?php
		}
		else if(AFTER_ROLL === $state)
		{
?>
<p>Choose what to do:</p>
<form method="POST" action="act.php">
	<input name="action" type="hidden" value="chooseSubstate">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<div class="form-group">
		<label for="chooseSubstateSubstate">Action</label>
		<select class="form-control" id="chooseSubstateSubstate" name="substate">
			<option value="buildRoad">Build a road</option>
			<option value="buildSettlement">Build a settlement</option>
			<option value="buildCity">Build a city</option>
			<option value="buyDevelopmentCard">Buy a development card</option>
			<option value="domesticTrade">Trade with a player</option>
			<option value="maritimeTrade">Trade with a port</option>
			<option value="useDevelopmentCard">Use a development card</option>
			<option value="endTurn">End the turn</option>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Choose Action">
	</div>
</form>
<?php
		}
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is choosing what to do.</p>
<?php
	}
?>
