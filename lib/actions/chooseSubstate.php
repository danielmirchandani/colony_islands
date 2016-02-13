<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to choose a substate");
	if(CHOOSE_ACTION !== $substate)
		colonyError("Game $gameID must be in substate CHOOSE_ACTION to choose a substate");

	if(!colonyPost("substate"))
		colonyError("Must provide a substate to choose a substate");
	$nextSubstate = $_POST["substate"];

	# Actions which require user input just change the state of the game while
	# all others include the code to evaluate immediately
	if(BEFORE_ROLL === $state)
	{
		if("rollDice" === $nextSubstate)
			include("lib/actions/rollDice.php");
		else if("useDevelopmentCard" === $nextSubstate)
			colonySetSubstate($gameID, USE_DEVELOPMENT_CARD);
		else
			colonyError("$nextSubstate is not a valid substate to choose a substate during the BEFORE_ROLL state");
	}
	else if(AFTER_ROLL === $state)
	{
		if("buildCity" === $nextSubstate)
			colonySetSubstate($gameID, BUILD_CITY);
		else if("buildRoad" === $nextSubstate)
			colonySetSubstate($gameID, BUILD_ROAD);
		else if("buildSettlement" === $nextSubstate)
			colonySetSubstate($gameID, BUILD_SETTLEMENT);
		else if("buyDevelopmentCard" === $nextSubstate)
			include("lib/actions/buyDevelopmentCard.php");
		else if("domesticTrade" === $nextSubstate)
			colonySetSubstate($gameID, DOMESTIC_TRADE);
		else if("endTurn" === $nextSubstate)
			include("lib/actions/endTurn.php");
		else if("maritimeTrade" === $nextSubstate)
			colonySetSubstate($gameID, MARITIME_TRADE);
		else if("useDevelopmentCard" === $nextSubstate)
			colonySetSubstate($gameID, USE_DEVELOPMENT_CARD);
		else
			colonyError("$nextSubstate is not a valid substate to choose a substate during the AFTER_ROLL state");
	}
?>
