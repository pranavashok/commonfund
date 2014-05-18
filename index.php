<?php
include("config.php");
$mysqli = new mysqli($host, $db_user, $db_password, $db_name);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
if( isset($_POST['item']) && isset($_POST['amount'])) {
	$sum = 0;
	$count = 0;
	$amount = $_POST['amount'];
	foreach($amount as $amt) {
		if($amt >= 0)
			$sum += $amt;
		else
			$count++;
	}

	$q = "INSERT INTO billdetails VALUES(DEFAULT, DEFAULT, '" . $_POST['item'] . "', ". $sum . ");";
	$mysqli->query($q);
	$id = $mysqli->insert_id;
	$q = "SELECT * FROM stakeholder";
	$result = $mysqli->query($q);

	$i = 0;
	while( $obj = $result->fetch_object() ) {
		if( $amount[$i] >= 0) {
			$q = "INSERT INTO billing VALUES(" . $id . ", " . $obj->ID . ", " . $amount[$i++] . ", 1);";
			$mysqli->query($q);
		}
		else {
			$q = "INSERT INTO billing VALUES(" . $id . ", " . $obj->ID . ", " . $sum/(count($amount)-$count) . ", 0);";
			$mysqli->query($q);
			$i++;
		}
	}
}
$debtors = array();
$creditors = array();
?>
<html>
<head>
<title>Accounts Manager</title>
<link href='http://fonts.googleapis.com/css?family=Raleway:200' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
	<?php include('header.php') ?>
	<div id="entry">
		<h4>NOTE THIS DOWN, SIR!</h4>
		<div class="tip">Tip: Entering -1 will exclude the person</div>
		<form id="entry-form" action="" method="POST">
			<table>
			<tr><td>We bought</td><td><input type="text" name="item"></input></td></tr>
			<?php
				$q = "SELECT * FROM stakeholder";
				$result = $mysqli->query($q);
				while($obj = $result->fetch_object()) {
					echo "<tr><td>" . $obj->Name . "</td><td><input type='text' name='amount[]' value='0'></input></td></tr>";
				}
			?>
			</table>
			<input class="button" type="submit" value="SUBMIT"></input>
		</form>
	</div>
	<div id="summary">
		<?php
			/*$q = 'SELECT Name, SUM(Amount-Average) AS Balance FROM billing
					JOIN (
					SELECT b.BillID, AVG(Amount) AS Average
					FROM billing b
					JOIN billdetails ON b.BillID = billdetails.BillID
					WHERE Valid =1
					GROUP BY b.BillID) AS j
					ON j.BillID = billing.BillID
					JOIN stakeholder s ON StakeholderID = ID
					GROUP BY StakeholderID';*/
			$q = '  SELECT Name, SUM(Balance) AS Balance 
					FROM 
						(
							SELECT StakeholderID, SUM(billing.Amount-Average) AS Balance FROM billing
							JOIN (
								SELECT b.BillID, AVG(Amount) AS Average
								FROM billing b
								JOIN billdetails ON b.BillID = billdetails.BillID
								WHERE Valid =1
								GROUP BY b.BillID
							) AS j
							ON j.BillID = billing.BillID
							GROUP BY StakeholderID
							UNION ALL
								(SELECT GiverID, Amount FROM transfers)
							UNION ALL
								(SELECT TakerID,-1*Amount FROM transfers)
							UNION ALL
								(SELECT TakerID, -1*Amount FROM debts)
							UNION ALL
								(SELECT LenderID, Amount FROM debts)
							UNION ALL
								(SELECT FromId, Amount FROM settlement)
							UNION ALL
								(SELECT ToId, -1*Amount FROM settlement)							
						) AS Balances
					JOIN stakeholder s ON StakeholderID = ID
					GROUP BY StakeholderID';
			$result = $mysqli->query($q);
			while($obj = $result->fetch_object()) {
				if($obj->Balance < 0)
					$debtors[] = $obj;
				else
					$creditors[] = $obj;
			}
		?>
		<h4>AS OF TODAY,</h4><br />
		<?php
			foreach($debtors as $d) {
				echo $d->Name . " owes <strong>" . round($d->Balance)*-1 . "</strong><br />\n\r";
			}
		?>
		<h5>TO THE COMMON FUND</h5><br />
		<?php
			foreach($creditors as $c) {
				echo $c->Name . " can take <strong>" . round($c->Balance) . "</strong><br />\n\r";
			}
		?>
		<h5>FROM THE COMMON FUND</h5>
	</div>	
</body>
