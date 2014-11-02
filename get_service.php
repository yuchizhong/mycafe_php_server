<?php
$storeID = $_GET["storeID"];
$tableID = $_GET["tableID"];
$serviceType = $_GET["type"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

mysql_query("START TRANSACTION");

//get customerID by customer
$result = mysql_query("SELECT * FROM service_call WHERE storeID='$storeID' AND tableID='$tableID' AND type='$serviceType' AND status=0");
$alreadyCalled = 0;
while ($row = mysql_fetch_array($result)) {
    $alreadyCalled = 1;
    break;
}
mysql_free_result($result);

$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

if ($alreadyCalled) {
    mysql_query("UPDATE service_call SET date='$current_date', time='$current_time' WHERE storeID='$storeID' AND tableID='$tableID' AND type='$serviceType'");
    echo "ERROR";
} else {
    $q = "INSERT INTO service_call VALUES ('$storeID', '$tableID', '$current_date', '$current_time', '$serviceType', 0)";
    $result = mysql_query($q);
    mysql_free_result($result);
    echo "OK";
}

mysql_query("COMMIT");

mysql_close($con);
?>
