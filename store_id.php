<?php 
$major = $_GET["major"];
$minor = $_GET["minor"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");
$result = mysql_query("SELECT storeID FROM beacons WHERE major='$major' AND minor='$minor'");
while ($row = mysql_fetch_array($result)) { 
	echo $row["storeID"];
	break;
} 
mysql_free_result($result); 
mysql_close($con);
?>
