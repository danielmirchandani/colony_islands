<?php
	if(AFTER_ROLL !== $state)
		colonyError("Game $gameID must be in state AFTER_ROLL to propose a trade");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
?>
<form id="selectForm" method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="domesticTrade">
	<p>Trade away</p>
	<div class="form-group">
		<label for="domesticTradeAwayBrick">Brick</label>
		<input class="form-control" id="domesticTradeAwayBrick" name="awayBrick" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeAwayGrain">Grain</label>
		<input class="form-control" id="domesticTradeAwayGrain" name="awayGrain" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeAwayLumber">Lumber</label>
		<input class="form-control" id="domesticTradeAwayLumber" name="awayLumber" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeAwayOre">Ore</label>
		<input class="form-control" id="domesticTradeAwayOre" name="awayOre" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeAwayWool">Wool</label>
		<input class="form-control" id="domesticTradeAwayWool" name="awayWool" type="text" value="0">
	</div>
	<p>for</p>
	<div class="form-group">
		<label for="domesticTradeForBrick">Brick</label>
		<input class="form-control" id="domesticTradeForBrick" name="forBrick" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeForGrain">Grain</label>
		<input class="form-control" id="domesticTradeForGrain" name="forGrain" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeForLumber">Lumber</label>
		<input class="form-control" id="domesticTradeForLumber" name="forLumber" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeForOre">Ore</label>
		<input class="form-control" id="domesticTradeForOre" name="forOre" type="text" value="0">
	</div>
	<div class="form-group">
		<label for="domesticTradeForWool">Wool</label>
		<input class="form-control" id="domesticTradeForWool" name="forWool" type="text" value="0">
	</div>
	<p>with</p>
	<div class="form-group">
		<label for="domesticTradeWithID">Player</label>
		<select class="form-control" id="domesticTradeWithID" name="withID">
<?php
		foreach($players as $player)
		{
			if($loggedIn["ID"] !== $player["ID"])
			{
?>
			<option value="<?php echo($player["ID"]);?>"><?php echo($player["displayName"]);?></option>
<?php
			}
		}
?>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Domestic Trade">
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
<p><?php echo($activePlayer["displayName"]);?> is proposing a trade with another player.</p>
<?php
	}
?>
