<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to build the second road of Road Building");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
?>
<form id="selectForm" method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="buildRoad">
	<input id="selectIDInput" name="roadID" type="hidden">
</form>
<p>Choose the location for your second road of Road Building.</p>
<?php
	}
	else
	{
?>
<p><?php echo($activePlayer["displayName"]);?> is selecting the location for the second road of Road Buildings.</p>
<?php
	}
?>
