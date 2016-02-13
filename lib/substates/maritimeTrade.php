<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to start maritime trade");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
?>
<form id="selectForm" method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="maritimeTrade">
	<div class="form-group">
		<label for="maritimeTradeAmount">Trade away amount</label>
		<select class="form-control" id="maritimeTradeAmount" name="amount">
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
		</select>
	</div>
	<div class="form-group">
		<label for="maritimeTradePayment">Trade away resource</label>
		<select class="form-control" id="maritimeTradePayment" name="payment">
			<option value="Brick">Brick</option>
			<option value="Grain">Grain</option>
			<option value="Lumber">Lumber</option>
			<option value="Ore">Ore</option>
			<option value="Wool">Wool</option>
		</select>
	</div>
	<div class="form-group">
		<label for="maritimeTradeReceive">Trade for resource</label>
		<select class="form-control" id="maritimeTradeReceive" name="receive">
			<option value="Brick">Brick</option>
			<option value="Grain">Grain</option>
			<option value="Lumber">Lumber</option>
			<option value="Ore">Ore</option>
			<option value="Wool">Wool</option>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Maritime Trade">
	</div>
</form>
<form method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="cancel">
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Cancel">
	</div>
</form>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is trading with a port.</p>
<?php
	}
?>
