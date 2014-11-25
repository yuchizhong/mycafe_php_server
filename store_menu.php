<?php

function setup_wifi($storeID, $ssid, $pass) {
	if (!is_dir('images/store' . $storeID))
		mkdir('images/store' . $storeID, 0777, true);

	if ($ssid == NULL || $ssid == "")
		return false;
	
	$path = 'images/store' . $storeID . '/wifi.mobileconfig';
	if (file_exists($path)) //already exist
		return true;

	$content = @file_get_contents('wifi.mobileconfig');
	if(!$content)
		return false;

	$content = str_replace('ssidssidssid', $ssid, $content);
	$content = str_replace('passpasspass', $pass, $content);
    
	return file_put_contents($path, $content) ? true : false;
}



$id = $_GET["id"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query("SELECT * FROM stores WHERE storeID='$id'");
$storeName = "";
$support = "";
$tableFlag = "";
$black = "";
$payOption = "";
$wifi_ssid = "";
$wifi_pass = "";
while ($row = mysql_fetch_array($result)) {
	$storeName = $row['storeName'];
	$support = $row['supportFlag'];
	$tableFlag = $row['tableFlag'];
	$black = $row['useBlackFont'];
	$payOption = $row['payOption'];
	$wifi_ssid = $row['wifiSSID'];
	$wifi_pass = $row['wifiPASS'];
	break;
}
mysql_free_result($result);
$result = mysql_query("SELECT * FROM beacons WHERE storeID='$id'");
$beacons = array();
while ($row = mysql_fetch_array($result)) {
        $b = array('major'=>$row['major'], 'minor'=>$row['minor']);
	array_push($beacons, $b);
}
mysql_free_result($result);
$result = mysql_query("SELECT * FROM dishes, dishCategory WHERE dishes.storeID='$id' AND dishes.storeID=dishCategory.storeID AND dishes.catagory=dishCategory.categoryID ORDER BY categoryID ASC, dishID ASC");
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
	$temp = array('dishID'=>$row['dishID'], 'image'=>$row['picPath'], 'name'=>$row['dishName'], 'catagory'=>$row['categoryName'], 'price'=>$row['price'], 'description'=>$row['description'], 'note'=>$row['note'], 'addition'=>$row['orderCount'] . ":" . $row['upCount']);
	array_push($arrlist, $temp);
}

$wifi_ok = '0';
if (setup_wifi($id, $ssid, $pass))
	$wifi_ok = '1';

$arr = array('storeName'=>$storeName, 'beacons'=>$beacons, 'wifi'=>$wifi_ok, 'payOption'=>$payOption, 'black'=>$black, 'support'=>$support, 'tableFlag'=>$tableFlag, 'menu'=>$arrlist);
echo json_encode($arr);
mysql_free_result($result); 
mysql_close($con);
?>
