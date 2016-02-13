<div id="playerStatus">
<?php
	$foundMatch = FALSE;
	foreach($allowedStates as $checkState => $stateFile)
	{
		if($state === $checkState)
		{
			include("lib/states/$stateFile.php");
			$foundMatch = TRUE;
			break;
		}
	}
	if(!$foundMatch)
		colonyError("State $state not allowed for game $gameID");

	$foundMatch = FALSE;
	foreach($allowedSubstates as $checkSubstate => $substateFile)
	{
		if($substate === $checkSubstate)
		{
			include("lib/substates/$substateFile.php");
			$foundMatch = TRUE;
			break;
		}
	}
	if(!$foundMatch)
		colonyError("Substate $substate not allowed for game $gameID");
?>
</div>
