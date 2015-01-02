<?php
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
$customerID = 0;
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$customer'");
while ($row = mysql_fetch_array($result)) {
    $customerID = $row['userID'];
    break;
}
mysql_free_result($result);

if (intval($customerID) == 0) {
	mysql_close($con);
	echo 'ERROR_USER';
	exit(1);
}

$arr = $json["order"];
$total = $json["total"];
$credit = $json["credit"];
$tableID = $json["table"];
$platform = $json["platform"];
if ($platform == NULL || $platform == "") {
	$platform == "0";
}

//get time
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

mysql_query("START TRANSACTION");

if ($platform != "1") {
    mysql_query("UPDATE orders SET orderFlag=3 WHERE payFlag=0 AND orderFlag=0 AND storeID='$id' AND customerID='$customerID' AND platform<>1");
}

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

$totalPrice = 0.0;
$result = mysql_query("SELECT SUM(totalPrice) FROM orders WHERE storeID='$id' AND customerID='$customerID' AND payFlag=0 AND orderFlag=0");
while ($row = mysql_fetch_array($result)) {
    $totalPrice = $row["SUM(totalPrice)"];
    break;
}
mysql_free_result($result);

mysql_query("COMMIT");

echo 'OK_' . $orderID . ':' . $totalPrice;

mysql_close($con);
?>