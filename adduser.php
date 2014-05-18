<?php
include("config.php");
$mysqli = new mysqli($host, $db_user, $db_password, $db_name);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
if( isset($_POST['Name'])) {
	$q = "INSERT INTO stakeholder VALUES(DEFAULT, '" . $_POST['Name'] . "');";
	if($mysqli->query($q))
		echo "Added " . $_POST['Name'] . ". ";
	else
		echo "Failed to add " . $_POST['Name'] . ". ";
}
?>
<html>
<head>
<title>Accounts Manager</title>
<link href='http://fonts.googleapis.com/css?family=Raleway:200' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
	<div id="header">
		<h1>COMMON FUND MANAGER</h1>
		<div id="nav"><a href="index.php">Add Entry</a> | <a href="adduser.php">Add User</a> | <a href="summary.php">View Summary</a> | <a href="transfers.php">Transfers</a></div>
	</div>
	<div class="line"></div>
	<div id="user">
		<h4>THIS GENTLEMAN WANTS TO JOIN THE PARTY!</h4>
		<form action="" method="POST">
			<input type="text" name="Name"></input>
			<input type="submit" value="Add him!"></input>
		</form>
	</div>
	<div id="summary">
	</div>
</body>