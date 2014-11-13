<?php
$raw = file_get_contents("php://input");

$con = mysql_connect("localhost", "root", "123456");
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

$arr = $json["order"];
$total = $json["total"];
$tableID = $json["table"];

//get time
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

mysql_query("START TRANSACTION");

$q = "INSERT INTO orders VALUES (NULL, '$id', '$current_date', '$current_time', '$tableID', '$customerID', '0', '0', '0', '$total', '0')";
$result = mysql_query($q);
mysql_free_result($result);

$orderID = -1; //get orderID back from database
$q = "SELECT MAX(orderID) FROM orders WHERE storeID='$id' AND customerID='$customerID'";
$result = mysql_query($q);
while ($row = mysql_fetch_array($result)) { 
    $orderID = $row["MAX(orderID)"];
    break;
}

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

mysql_query("COMMIT");

echo $orderID;

mysql_close($con);
?>
