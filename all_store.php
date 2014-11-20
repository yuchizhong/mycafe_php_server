<?php 
$province = $_GET["province"];
$city = $_GET["city"];
$district = $_GET["district"];
$lon = $_GET["longitude"];
$lat = $_GET["latitude"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

//$q = "SELECT * FROM all_stores WHERE province='$province' AND city='$city' AND district='$district' ORDER BY matchingStore DESC";
//if ($province === 'ALL')
//	$q = "SELECT * FROM stores ORDER BY supportFlag DESC, storeID DESC";
//elseif ($city === 'ALL')
//	$q = "SELECT * FROM all_stores WHERE province='$province' ORDER BY matchingStore DESC";
//elseif ($district === 'ALL')
//	$q = "SELECT * FROM all_stores WHERE province='$province' AND city='$city' ORDER BY matchingStore DESC";

$q = "SELECT * FROM stores WHERE storeID IS NOT NULL AND addr IS NOT NULL ORDER BY ACOS(SIN(('$lat' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) + COS(('$lat' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('$lon' * 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 ASC, storeID DESC LIMIT 20";
$result = mysql_query($q);
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
	$spt = $row['supportFlag'];
	/*
	if ($spt == "0")
		$spt = "1";
	else
		$spt = "0";
	 */
	$arr = array("longitude"=>$row['longitude'], "latitude"=>$row['latitude'], "support"=>$spt, 'black'=>$row['useBlackFont'], "name"=>$row['storeName'], "ID"=>$row['storeID'], "address"=>$row['addr'], "image"=>$row['logoFile'],  "rating"=>'5', "avgPrice"=>'30', "tel"=>$row['tel'], "businessTime"=>$row['businessHour'], "desp"=>$row['description']);
	array_push($arrlist, $arr);
}
$arr = array('list'=>$arrlist);
echo json_encode($arr);
mysql_free_result($result); 

mysql_close($con);
?>
