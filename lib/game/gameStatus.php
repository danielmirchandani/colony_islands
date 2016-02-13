<div id="gameStatus">
<h1>Game Status</h1>
<table class="table">
	<tr>
		<th></th>
		<th>Name</th>
		<th><abbr title="Resource Cards">R</abbr></th>
		<th><abbr title="Hidden Development Cards">HD</abbr></th>
		<th><abbr title="Used Development Cards">UD</abbr></th>
		<th><abbr title="Longest Road">LR</abbr></th>
		<th><abbr title="Largest Army">LA</abbr></th>
		<th><abbr title="Visible Points">VP</abbr></th>
	</tr>
<?php
		foreach($players as $player)
		{
			$backgroundClass = "playerColor" . $player["colorID"];
			$dataPlayIndex = "data-player-index=\"" . $player["playIndex"] . "\"";
?>
	<tr class="<?php echo($backgroundClass);?>">
		<td class="togglePlayerInfo" <?php echo($dataPlayIndex);?>><span class="caret"></span></td>
		<td><?php echo($player["displayName"]);?></td>
		<td><?php echo($player["resourceCardCount"]);?></td>
		<td><?php echo($player["unusedDevelopmentCardCount"]);?></td>
		<td><?php echo($player["usedDevelopmentCardCount"]);?></td>
		<td><?php if($player["ID"] === $longestRoadID) echo($longestRoadAmount);?></td>
		<td><?php if($player["ID"] === $largestArmyID) echo($largestArmyAmount);?></td>
		<td><?php echo($player["visiblePoints"]);?></td>
	</tr>
	<tr class="hidden playerInfo <?php echo($backgroundClass);?>" <?php echo($dataPlayIndex);?>>
		<td colspan="8">
			<ul>
				<li>Roads remaining: <?php echo(($roadLimit - $player["roadCount"]));?></li>
				<li>Settlements remaining: <?php echo(($settlementLimit - $player["settlementCount"]));?></li>
				<li>Cities remaining: <?php echo(($cityLimit - $player["cityCount"]));?></li>
<?php
			if(0 < count($player["usedDevelopmentCards"]))
			{
?>
				<li>
					Used development cards:
					<ul>
<?php
				foreach($player["usedDevelopmentCards"] as $card)
				{
?>
						<li><?php echo(preg_replace($CARD_PATTERNS, $CARD_IMAGE_HTML, $card));?></li>
<?php
				}
?>
					</ul>
				</li>
<?php
			}
?>
				<li><a href="stats.php?playerID=<?php echo($player["ID"]);?>">Stats</a></li>
			</ul>
		</td>
	</tr>
<?php
		}
?>
</table>
<?php
	$statement = $db->prepare("
		SELECT
			COUNT(*) as `amount`,
			`type`
		FROM `col_resource_cards`
		WHERE
			`gameID` = :game AND
			`playerID` = '0'
		GROUP BY `type`
	");
	$statement->bindValue("game", $gameID);
	$statement->execute();

	$amounts = array(
		"Brick" => 0,
		"Grain" => 0,
		"Lumber" => 0,
		"Ore" => 0,
		"Wool" => 0,
	);
	while(FALSE !== ($row = $statement->fetch()))
	{
		$type = $row["type"];
		$amounts[$type] = intval($row["amount"]);
	}
	$statement->closeCursor();
?>
<table class="table">
	<tr>
		<th>Resources</th>
		<th class="Brick">Brick</th>
		<th class="Grain">Grain</th>
		<th class="Lumber">Lumber</th>
		<th class="Ore">Ore</th>
		<th class="Wool">Wool</th>
	</tr>
	<tr>
		<td>Bank</td>
		<td class="Brick"><?php echo($amounts["Brick"]);?></td>
		<td class="Grain"><?php echo($amounts["Grain"]);?></td>
		<td class="Lumber"><?php echo($amounts["Lumber"]);?></td>
		<td class="Ore"><?php echo($amounts["Ore"]);?></td>
		<td class="Wool"><?php echo($amounts["Wool"]);?></td>
	</tr>
	<tr>
		<td>Road</td>
		<td class="Brick">1</td>
		<td class="Grain">0</td>
		<td class="Lumber">1</td>
		<td class="Ore">0</td>
		<td class="Wool">0</td>
	</tr>
	<tr>
		<td>Settlement</td>
		<td class="Brick">1</td>
		<td class="Grain">1</td>
		<td class="Lumber">1</td>
		<td class="Ore">0</td>
		<td class="Wool">1</td>
	</tr>
	<tr>
		<td>City</td>
		<td class="Brick">0</td>
		<td class="Grain">2</td>
		<td class="Lumber">0</td>
		<td class="Ore">3</td>
		<td class="Wool">0</td>
	</tr>
	<tr>
		<td>Development Card</td>
		<td class="Brick">0</td>
		<td class="Grain">1</td>
		<td class="Lumber">0</td>
		<td class="Ore">1</td>
		<td class="Wool">1</td>
	</tr>
</table>
</div>
