<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to move the robber");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
?>
<form id="selectForm" method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="moveRobber">
	<input id="selectIDInput" name="tileID" type="hidden">
</form>
<p>Choose the location for the robber.</p>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is moving the robber.</p>
<?php
	}
?>
