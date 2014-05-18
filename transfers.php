<?php
include("config.php");
$mysqli = new mysqli($host, $db_user, $db_password, $db_name);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
?>
<html>
<head>
<title>Accounts Manager</title>
<link href='http://fonts.googleapis.com/css?family=Raleway:200' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="style.css" type="text/css" />
<link href="css/ui-lightness/jquery-ui-1.10.3.custom.css" rel="stylesheet">
<script src="js/jquery-1.9.1.js"></script>
</head>
<body>
	<?php include('header.php') ?>
	<div id="transfers">
		<?php 
		if (isset($_POST['giver']) && isset($_POST['taker']) && isset($_POST['amount'])) {
			$q = "INSERT INTO transfers VALUES(DEFAULT, " . $_POST['giver'] . ", " . $_POST['taker'] . ", " . $_POST['amount'] . ", DEFAULT);";
			if($mysqli->query($q)) {
				echo "Success. <a href='index.php'>Go back!</a>";
			}
		} 
		else {
			echo '<form action="" method="post">
				<select name="giver" id="giver">';
				$q = "SELECT ID, Name FROM stakeholder";
				$result = $mysqli->query($q);
				while($obj = $result->fetch_object()) {
					echo "<option value=" . $obj->ID . ">" . $obj->Name . "</option>";
				}
			echo '</select>	transferred <input type="text" name="amount"></input> to
			<select name="taker" id="taker">';
				$q = "SELECT ID, Name FROM stakeholder";
				$result = $mysqli->query($q);
				while($obj = $result->fetch_object()) {
					echo "<option value=" . $obj->ID . ">" . $obj->Name . "</option>";
				}
			echo '</select>
				<input type="submit" value="Go"></input>
				</form>';
		}
		?>
	</div>
</body>
</html>
