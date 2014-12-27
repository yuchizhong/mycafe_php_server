<?php 
$major = $_GET["major"];
$minor = $_GET["minor"];

$notification = "";

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query("SELECT storeName FROM stores, beacons WHERE major='$major' AND minor='$minor' AND beacons.storeID=stores.storeID");
while ($row = mysql_fetch_array($result))
{ 
	$notification = '欢迎来到：' . $row['storeName'];
	break;
} 

if ($notification == NULL)
	$notification = "";

echo $notification;

mysql_free_result($result); 
mysql_close($con);
?>
