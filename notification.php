<?php 
$major = $_GET["major"];
$minor = $_GET["minor"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");
$result = mysql_query("SELECT notification FROM stores, beacons WHERE major='$major' AND minor='$minor' AND beacons.beaconID=stores.beaconID");
while ($row = mysql_fetch_array($result))
{ 
	echo $row["notification"];
	break;
} 
mysql_free_result($result); 
mysql_close($con);
?>
