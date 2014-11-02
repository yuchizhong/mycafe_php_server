<?php 
$province = $_GET["province"];
$city = $_GET["city"];
$district = $_GET["district"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

//$q = "SELECT * FROM all_stores WHERE province='$province' AND city='$city' AND district='$district' ORDER BY matchingStore DESC";
//if ($province === 'ALL')
	$q = "SELECT * FROM stores ORDER BY supportFlag DESC, storeID DESC";
//elseif ($city === 'ALL')
//	$q = "SELECT * FROM all_stores WHERE province='$province' ORDER BY matchingStore DESC";
//elseif ($district === 'ALL')
//	$q = "SELECT * FROM all_stores WHERE province='$province' AND city='$city' ORDER BY matchingStore DESC";
	
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
	$arr = array("support"=>$spt, "name"=>$row['storeName'], "ID"=>$row['storeID'], "address"=>$row['addr'], "image"=>$row['logoFile'],  "rating"=>'5', "avgPrice"=>'100', "tel"=>$row['tel'], "businessTime"=>$row['businessHour'], "desp"=>$row['description']);
	array_push($arrlist, $arr);
}
$arr = array('list'=>$arrlist);
if (!count($arrlist) == 0)
	echo json_encode($arr);
mysql_free_result($result); 

mysql_close($con);
?>
