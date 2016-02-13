<div id="messages">
<h1>Messages</h1>
<form action="act.php" method="POST">
	<input name="action" type="hidden" value="postMessage">
	<input name="gameID" type="hidden" value="<?php echo($gameID);?>">
	<div class="form-group">
		<label for="postMessageMessage">Message to post</label>
		<textarea class="form-control" id="postMessageMessage" name="message"></textarea>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Post Message">
	</div>
</form>
<ul>
<?php
	foreach($messages as $message)
	{
		$text = preg_replace($CARD_PATTERNS, $CARD_IMAGE_HTML, $message["text"]);

		if(1 === $message["isSay"])
		{
?>
	<li><?php echo($message["time"]);?> - <?php echo($message["playerName"]);?>: <strong><?php echo($text);?></strong></li>
<?php
		}
		else
		{
?>
	<li><?php echo($message["time"]);?> - <?php echo($message["playerName"]);?> <?php echo($text);?></li>
<?php
		}
	}
	$statement->closeCursor();
?>
</ul>
<p><a href="messages.php?gameID=<?php echo($gameID);?>">View all messages</a></p>
</div>
