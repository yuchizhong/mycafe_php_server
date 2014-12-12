<?php 
$major = $_GET["major"];
$minor = $_GET["minor"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

$q = "SELECT beacons.storeID, storeName, beacons.beaconID FROM stores, beacons WHERE major='$major' AND minor='$minor' AND beacons.storeID=stores.storeID";
$result = mysql_query($q);
$storeID = "";
$storeName = "";
while ($row = mysql_fetch_array($result)) {
	$storeID = $row['storeID'];
	$storeName = $row['storeName'];
	break;
}
mysql_free_result($result); 

$q = "SELECT major, minor, beaconID FROM beacons WHERE storeID='$storeID'";
$result = mysql_query($q);
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
	$arr = array('beaconID'=>$row['beaconID'], "major"=>$row['major'], "minor"=>$row['minor']);
	array_push($arrlist, $arr);
}
$arr = array('storeID'=>$storeID, 'storeName'=>$storeName, 'beacons'=>$arrlist);
echo json_encode($arr);
mysql_free_result($result); 

mysql_close($con);
?>
