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
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script src="js/jquery-1.9.1.js"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
      	$.ajax({
	        url: "charts.php",
	        data: {spending:'1'},
	        dataType:"json",
	        success: function(jsonData) {
					data = new google.visualization.DataTable(jsonData);
			        var options = {
			          title: 'Spending by Person', 
			          width: 450, 
			          height: 300,
			          legend: {position: 'right', alignment: 'center'}
			        };
			        var chart = new google.visualization.PieChart(document.getElementById('chart_spending'));
			        chart.draw(data, options);
	        },
	        error: function (jqXHR, textStatus, errorThrown) {
	            alert(textStatus + '\n' + errorThrown);
	            if (!$.browser.msie) {
	                console.log(jqXHR);
	            }
	        }
      	});
      	$.ajax({
	        url: "charts.php",
	        data: {bills:'1'},
	        dataType:"json",
	        success: function(jsonData) {
					data = new google.visualization.DataTable(jsonData);
			        var options = {
			          title: 'Spending by Item', 
			          width: 400, 
			          height: 300,
			          legend: {position: 'right', alignment: 'center'}
			        };
			        var chart = new google.visualization.PieChart(document.getElementById('chart_bills'));
			        chart.draw(data, options);
	        },
	        error: function (jqXHR, textStatus, errorThrown) {
	            alert(textStatus + '\n' + errorThrown);
	            if (!$.browser.msie) {
	                console.log(jqXHR);
	            }
	        }
      	});
      }
    </script>
</head>
<body>
	<?php include('header.php') ?>
	<div class="summary" id="bills">
		<h4>WE HAVE SPENT ON</h4>
		<table>
			<tr><th>Date</th><th>Particulars</th><th>Total</th></tr>
			<?php
				$q = 'SELECT Date, Particulars, SUM(Amount) AS Total FROM billing 
						JOIN billdetails ON billing.BillID = billdetails.BillID AND Valid = 1
						GROUP BY billing.BillID';
				$result = $mysqli->query($q);
				while($obj = $result->fetch_object()) {
					echo "<tr><td>" . date('F j, Y', strtotime($obj->Date)) . "</td><td>" . $obj->Particulars . "</td><td>" . $obj->Total . "</td></tr>";
				}
			?>
		</table>
	</div>
	<div class="summary" id="spending">
		<h4>WHO HAS SPENT WHAT</h4>
		<table>
			<tr><th>Name</th><th>Amount Spent</th></tr>
			<?php
				$q = 'SELECT Name, SUM(Amount) AS Spent FROM billing 
						JOIN stakeholder ON StakeholderID = ID
						WHERE Valid = 1 GROUP BY StakeholderID';
				$result = $mysqli->query($q);
				$sum = 0;
				while($obj = $result->fetch_object()) {
					echo "<tr><td>" . $obj->Name . "</td><td>" . $obj->Spent . "</td></tr>";
					$sum += intval($obj->Spent);
				}
				echo "<tr><th>Total</th><th>" . $sum . "</th></tr>";
			?>
		</table>
	</div>
	<div class="summary" id="balance">
		<h4>THE BALANCE SHEET</h4>
		<table>
			<tr><th>Name</th><th>Balance</th></tr>
			<?php
				$q = 'SELECT Name, SUM(Balance) AS Balance 
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
							UNION
								(SELECT GiverID, Amount FROM transfers)
							UNION
								(SELECT TakerID,-1*Amount FROM transfers)
						) AS Balances
					JOIN stakeholder s ON StakeholderID = ID
					GROUP BY StakeholderID';
				$result = $mysqli->query($q);
				while($obj = $result->fetch_object()) {
					echo "<tr><td>" . $obj->Name . "</td><td>" . round($obj->Balance) . "</td></tr>";
				}
			?>
		</table>
	</div>
	<div class="summary" id="actualspending">
		<h4>ACTUAL SPENDING</h4>
		<table>
			<tr><th>Name</th><th>Balance</th></tr>
			<?php
				$q = '	SELECT Name, SUM( X.Balance ) AS Balance
						FROM (
							SELECT Name, SUM( Amount ) AS Balance
							FROM  `billing` 
							JOIN stakeholder ON billing.StakeholderID = stakeholder.ID
							WHERE Valid =1
							GROUP BY StakeholderID
							UNION (
								SELECT Name, -1 * SUM( Balance ) AS Balance
								FROM (
									SELECT StakeholderID, SUM( billing.Amount - Average ) AS Balance
									FROM billing
									JOIN (
										SELECT b.BillID, AVG( Amount ) AS Average
										FROM billing b
										JOIN billdetails ON 
											b.BillID = billdetails.BillID
										WHERE Valid =1
										GROUP BY b.BillID
									) AS j ON j.BillID = billing.BillID
									GROUP BY StakeholderID
								) AS Balances
								JOIN stakeholder s ON StakeholderID = ID
								GROUP BY StakeholderID
							)
						) AS X
						GROUP BY Name';
				$result = $mysqli->query($q);
				while($obj = $result->fetch_object()) {
					echo "<tr><td>" . $obj->Name . "</td><td>" . round($obj->Balance) . "</td></tr>";
				}
			?>
		</table>
	</div>
	<div id="chart_spending"></div>
	<div id="chart_bills"></div>
</body>