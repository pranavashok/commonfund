<?php
include("config.php");
$mysqli = new mysqli($host, $db_user, $db_password, $db_name);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
if( isset($_GET['spending'])) {
	$q = 'SELECT Name, SUM(Amount) AS Spent FROM billing 
		  JOIN stakeholder ON StakeholderID = ID
		  WHERE Valid = 1 GROUP BY StakeholderID';
	$result = $mysqli->query($q);
	$json = array();
	$tmp = array(array("id" => "name", "label" => "Name", "type" => "string"), array("id" => "spending", "label" => "Spending", "type" => "number"));
	$json['cols'] = $tmp;
	array_push($json, $columns);
	$rows = array();
	while($row = $result->fetch_object()){
	    $rows[] = array("c" => array(array("v" => $row->Name), array("v" => intval($row->Spent))));
	}
	$json['rows'] = $rows;
	echo json_encode($json);
}
if( isset($_GET['bills'])) {
	$q = 'SELECT T.Particulars, SUM(T.Total) AS Total FROM (SELECT Date, Particulars, SUM(Amount) AS Total FROM billing 
	JOIN billdetails ON billing.BillID = billdetails.BillID AND Valid = 1
	GROUP BY billing.BillID) AS T GROUP BY T.Particulars
	ORDER BY Total DESC';
	$result = $mysqli->query($q);
	$json = array();
	$tmp = array(array("id" => "particulars", "label" => "Particulars", "type" => "string"), array("id" => "spent", "label" => "Amount Spent", "type" => "number"));
	$json['cols'] = $tmp;
	array_push($json, $columns);
	$rows = array();
	while($row = $result->fetch_object()){
	    $rows[] = array("c" => array(array("v" => $row->Particulars), array("v" => intval($row->Total))));
	}
	$json['rows'] = $rows;
	echo json_encode($json);
}
?>