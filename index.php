<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyHTMLStart();
?>
<div class="row">
<div class="col-xs-12">
<ol class="breadcrumb">
	<li class="active">Home</li>
</ol>
</div>
</div>
<div class="row">
<div class="col-xs-12">
<h1>Welcome to Colony Islands</h1>
<p>Logged in as <?php echo($loggedIn["displayName"]);?> (<?php echo($loggedIn["emailAddress"]);?>)</p>
<p>You are currently playing in the following games:</p>
<ul>
<?php
	$statement = $db->prepare("
		SELECT
			col_games.ID,
			col_playing.isJoined,
			(col_games.state != :waiting AND col_games.activePlayerIndex = col_playing.playIndex) AS yourTurn
		FROM col_games
		LEFT JOIN col_playing ON col_games.ID = col_playing.gameID
		WHERE
			col_games.state != :complete AND
			col_playing.playerID = :ID AND
			col_games.isHidden != 1
		ORDER BY col_games.ID ASC
	");
	$statement->bindValue("waiting", WAITING_FOR_PLAYERS);
	$statement->bindValue("complete", COMPLETE);
	$statement->bindValue("ID", $loggedIn["ID"]);
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$gameID = intval($row["ID"]);
		$joined = intval($row["isJoined"]);
		$yourTurn = intval($row["yourTurn"]);

		if(0 === $joined)
			$description = " - waiting for you to join the game";
		else if(1 === $yourTurn)
			$description = " - you are currently the active player";
		else
			$description = "";
?>
	<li><a href="game.php?gameID=<?php echo($gameID);?>"><?php echo($gameID);?></a><?php echo($description);?></li>
<?php
	}
	$statement->closeCursor();
?>
</ul>
</div>
</div>
<div class="row">
<div class="col-xs-12 col-md-6">
<h1>Create game</h1>
<form action="createGame.php" method="POST">
	<p>Please choose a color:</p>
<?php
		foreach($colors as $colorID => $colorName)
		{
?>
	<div class="playerColor<?php echo($colorID);?> radio">
		<label>
			<input name="colorID" type="radio" value="<?php echo($colorID);?>">
			<?php echo($colorName);?>
		</label>
	</div>
<?php
		}
?>
	<div class="form-group">
		<label for="createGameOpponent1">Opponent 1 email address</label>
		<input class="form-control" id="createGameOpponent1" name="opponent1" type="text">
	</div>
	<div class="form-group">
		<label for="createGameOpponent2">Opponent 2 email address (optional)</label>
		<input class="form-control" id="createGameOpponent2" name="opponent2" type="text">
	</div>
	<div class="form-group">
		<label for="createGameOpponent3">Opponent 3 email address (optional)</label>
		<input class="form-control" id="createGameOpponent3" name="opponent3" type="text">
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Create Game">
	</div>
</form>
</div>
<div class="col-xs-12 col-md-6">
<h1>Change information</h1>
<form action="changeInfo.php" method="POST">
	<div class="form-group">
		<label for="changeInfoDisplayName">Display name</label>
		<input class="form-control" id="changeInfoDisplayName" name="displayName" type="text" value="<?php echo($loggedIn["displayName"]);?>">
	</div>
	<div class="form-group">
		<label for="changeInfoEmailAddress">E-mail address</label>
		<input class="form-control" id="changeInfoEmailAddress" name="emailAddress" type="text" value="<?php echo($loggedIn["emailAddress"]);?>">
	</div>
	<div class="form-group hidden">
		<label for="changeInfoTheme">Theme</label>
		<select class="form-control" id="changeInfoTheme" name="theme">
<?php
	foreach($themes as $themeIndex => $themeName)
	{
		if($loggedIn["theme"] === $themeIndex)
			$selected = "selected=\"selected\"";
		else
			$selected = "";
?>
			<option value="<?php echo($themeIndex);?>" <?php echo($selected);?>><?php echo($themeName);?></option>
<?php
	}
?>
		</select>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Submit">
	</div>
</form>
</div>
</div>
<?php
	if(1 === $loggedIn["isAdmin"])
	{
?>
<div class="row">
<div class="col-xs-12 col-md-6">
<h1>Unfinished games</h1>
<ul>
<?php
		$statement = $db->prepare("
			SELECT ID
			FROM col_games
			WHERE
				state != :state AND
				isHidden != 1
			ORDER BY ID ASC
		");
		$statement->bindValue("state", COMPLETE);
		$statement->execute();

		while(FALSE !== ($row = $statement->fetch()))
		{
			$gameID = intval($row["ID"]);
?>
	<li><a href="game.php?gameID=<?php echo($gameID);?>"><?php echo($gameID);?></a></li>
<?php
		}
?>
</ul>
</div>
<div class="col-xs-12 col-md-6">
<h1>Create player</h1>
<form action="createPlayer.php" method="POST">
	<div class="form-group">
		<label for="createPlayerDisplayName">Display name</label>
		<input class="form-control" id="createPlayerDisplayName" name="displayName" type="text">
	</div>
	<div class="form-group">
		<label for="createPlayerEmailAddress">E-mail address</label>
		<input class="form-control" id="createPlayerEmailAddress" name="emailAddress" type="text">
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Create Player">
	</div>
</form>
<h1>Alert everyone</h1>
<form action="alertEveryone.php" method="POST">
	<div class="form-group">
		<label for="alertEveryoneSubject">Subject</label>
		<input class="form-control" id="alertEveryoneSubject" name="subject" type="text" required>
	</div>
	<div class="form-group">
		<label for="alertEveryoneMessage">Message</label>
		<textarea class="form-control" id="alertEveryoneMessage" name="message" required></textarea>
		<p class="help-block">Remember that the message is wrapped with &lt;p&gt; and &lt;/p&gt; tags.</p>
	</div>
	<div class="form-group">
		<input class="btn btn-primary" type="submit" value="Alert Everyone">
	</div>
</form>
</div>
</div>
<div class="row">
<div class="col-xs-12">
	<p>Colony Islands is <a href="https://github.com/danielmirchandani/colony_islands">open-source</a>.</p>
</div>
</div>
<?php
	}
	colonyHTMLEnd();
?>
