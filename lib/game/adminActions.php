<div id="adminActions">
<h1>Admin Actions</h1>
<form method="POST" action="deleteGame.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Delete Game">
	</div>
</form>
<form method="POST" action="forceRoll.php">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<div class="form-group">
		<label for="forceRollRoll">Roll:</label>
		<input class="form-control" id="forceRollRoll" name="roll" type="text">
	</div>
	<div>
		<input class="btn btn-primary" type="submit" value="Force Roll">
	</div>
</form>
</div>
