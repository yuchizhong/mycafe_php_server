<?php
require(dirname(__FILE__) . '/encryption.php');

$raw = file_get_contents("php://input");

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

//read from raw JSON
if (get_magic_quotes_gpc()) {
    $raw = stripslashes($raw);
}
$json = json_decode($raw, true);
$id = $json["id"];
$customer = $json["customer"];
$username = $json["username"];
$getcredit = $json["getcredit"];
$customerID = 0;
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$customer'");
while ($row = mysql_fetch_array($result)) {
    $customerID = intval($row['userID']);
    break;
}
mysql_free_result($result);

$userID = 0;
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$username'");
while ($row = mysql_fetch_array($result)) {
    $userID = intval($row['userID']);
    break;
}
mysql_free_result($result);

if ($customerID <= 0) {
	mysql_close($con);
	echo 'ERROR_USER';
	exit(1);
}

if ($userID <= 0 && $username !=NULL && $username != "") {
	mysql_close($con);
        echo 'ERROR_CREDIT_USER';
        exit(1);
}

$arr = $json["order"];
$total = $json["total"];
$credit = $json["credit"];
$tableID = $json["table"];
$platform = $json["platform"];
if ($platform == NULL || $platform == "") {
	$platform == "1";
}

//get time
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

mysql_query("START TRANSACTION");

$q = "INSERT INTO orders VALUES (NULL, '$id', '$current_date', '$current_time', '$tableID', '$customerID', '0', '0', '0', '$total', '$credit', '0', '$platform')";
$result = mysql_query($q);
mysql_free_result($result);

$orderID = -1; //get orderID back from database
$q = "SELECT MAX(orderID) FROM orders WHERE storeID='$id' AND customerID='$customerID'";
$result = mysql_query($q);
while ($row = mysql_fetch_array($result)) { 
    $orderID = $row["MAX(orderID)"];
    break;
}
mysql_free_result($result);

foreach ($arr as $value) {
    $quantity = $value["quantity"];
    $dishID = $value["dishID"];
    
    $q = "INSERT INTO orderDetails VALUES ('$dishID', '$id', '$orderID', '$quantity')";
    $result = mysql_query($q);
    mysql_free_result($result);

    $q = "UPDATE dishes SET orderCount=orderCount+$quantity WHERE dishID='$dishID' AND storeID='$id'";
    $result = mysql_query($q);
    mysql_free_result($result);
}

if (intval($getcredit) == 1 && $userID > 0) {
    //add credit///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $totalCredit = 0;
    $sq = "SELECT totalCredit FROM orders WHERE orderID='$orderID'";
    $result = mysql_query($sq);
    while ($row = mysql_fetch_array($result)) {
        $totalCredit += intval($row["totalCredit"]);
    }
    mysql_free_result($result);
    if ($totalCredit > 0) {
    //get chain_id by storeID
    $chainID = 0;
    $result = mysql_query("SELECT chain_id FROM stores WHERE storeID='$id'");
    while ($row = mysql_fetch_array($result)) {
        $chainID = intval($row["chain_id"]);
        break;
    }
    mysql_free_result($result);
    //insert into credit if not exist
    $creditExist = false;
    $result = mysql_query("SELECT * FROM credit WHERE user_id='$userID' AND chain_id='$chainID'");
    while ($row = mysql_fetch_array($result)) {
        $creditExist = true;
        break;
    }
    mysql_free_result($result);
    if (!$creditExist) {
        mysql_query("INSERT INTO credit VALUES ('$chainID', '$userID', '0', '0', '0')");
    }
    
    mysql_query("UPDATE credit SET credit=credit+'$totalCredit', accumulated_credit=accumulated_credit+'$totalCredit' WHERE user_id='$userID' AND chain_id='$chainID'");
    
    //update member_level
    $memlv = 0;
    $acccredit = 0;
    $result = mysql_query("SELECT accumulated_credit FROM credit WHERE user_id='$userID' AND chain_id='$chainID'");
    while ($row = mysql_fetch_array($result)) {
        $acccredit = intval($row["accumulated_credit"]);
        break;
    }
    mysql_free_result($result);
    
    //calculate level
    $result = mysql_query("SELECT MAX(member_level) FROM creditLevel WHERE chain_id='$chainID' AND level_credits<='$acccredit'");
    while ($row = mysql_fetch_array($result)) {
        $memlv = intval($row["MAX(member_level)"]);
        break;
    }
    mysql_free_result($result);

    mysql_query("UPDATE credit SET member_level='$memlv' WHERE user_id='$userID' AND chain_id='$chainID'");
    }
    //add credit end///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

mysql_query("COMMIT");

echo 'OK_' . $orderID;

mysql_close($con);
?>
