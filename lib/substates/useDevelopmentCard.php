<?php
	if((BEFORE_ROLL !== $state) && (AFTER_ROLL !== $state))
		colonyError("Game $gameID must be in state BEFORE_ROLL or AFTER_ROLL to use a development card");

	if((NULL !== $me) && ($activePlayerIndex === $me["playIndex"]))
	{
?>
<p>Choose a development card to use.</p>
<form method="POST" action="act.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<input name="action" type="hidden" value="useDevelopmentCard">
	<div class="form-group">
		<label for="useDevelopmentCardDevelopmentCardID">Use development card</label>
		<select class="form-control" id="useDevelopmentCardDevelopmentCardID" name="developmentCardID">
<?php
	$statement = $db->prepare("
		SELECT
			`ID`,
			`type`
		FROM `col_development_cards`
		WHERE
			`gameID` = :game AND
			`playerID` = :player AND
			`turnUsed` = '0'
	");
	$statement->bindValue("game", $gameID);
	$statement->bindValue("player", $loggedIn["ID"]);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$devID = intval($row["ID"]);
		$devType = htmlspecialchars($row["type"]);
?>
			<option value="<?php echo($devID);?>"><?php echo($devType);?></option>
<?php
	}
	$statement->closeCursor();
?>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Use Development Card">
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
<p><?php echo($activePlayer["displayName"]);?> is using a development card.</p>
<?php
	}
?>
