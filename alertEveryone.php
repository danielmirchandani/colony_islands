<?php
	require("lib/common.php");
	list($db, $loggedIn) = colonyAuthenticate(TRUE);

	if(!colonyPost("subject") || !colonyPost("message"))
		colonyError("Must provide a subject and a message to alert everyone");

	$message = htmlspecialchars($_POST["message"]);
	$subject = htmlspecialchars($_POST["subject"]);

	$statement = $db->prepare("
		SELECT
			displayName,
			emailAddress
		FROM col_players
	");
	$statement->execute();

	while(FALSE !== ($row = $statement->fetch()))
	{
		$displayName = htmlspecialchars($row["displayName"]);
		$emailAddress = htmlspecialchars($row["emailAddress"]);

		colonyAlertPlayer($displayName, $emailAddress, $subject, $message);
	}
	$statement->closeCursor();

	header("Location: index.php");
?>
