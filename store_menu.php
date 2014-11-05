<?php 
$id = $_GET["id"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query("SELECT * FROM stores WHERE storeID='$id'");
$storeName = "";
$support = "";
$tableFlag = "";
$black = "";
while ($row = mysql_fetch_array($result)) {
	$storeName = $row['storeName'];
	$support = $row['supportFlag'];
	$tableFlag = $row['tableFlag'];
	$black = $row['useBlackFont'];
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
/*
if ($support == "0")
	$support = "1";
else
	$support = "0";
 */
$arr = array('storeName'=>$storeName, 'beacons'=>$beacons, 'black'=>$black, 'support'=>$support, 'tableFlag'=>$tableFlag, 'menu'=>$arrlist);
echo json_encode($arr);
mysql_free_result($result); 
mysql_close($con);
?>
