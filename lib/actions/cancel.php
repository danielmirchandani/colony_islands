<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID is not in state FIRST_CHOICE, SECOND_CHOICE, or AFTER_ROLL for action $action");
	if(
		(BUILD_CITY !== $substate) &&
		(BUILD_ROAD !== $substate) &&
		(BUILD_SETTLEMENT !== $substate) &&
		(DOMESTIC_TRADE !== $substate) &&
		(MARITIME_TRADE !== $substate) &&
		(USE_DEVELOPMENT_CARD !== $substate)
	)
		colonyError("Game $gameID is not in substate BUILD_CITY, BUILD_ROAD, BUILD_SETTLEMENT, DOMESTIC_TRADE, MARITIME_TRADE, or USE_DEVELOPMENT_CARD to cancel an action");

	colonySetSubstate($gameID, CHOOSE_ACTION);
?>
