<?php
$customer = $_GET["ID"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

$customerID = 0;
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$customer'");
while ($row = mysql_fetch_array($result)) {
    $customerID = $row['userID'];
    break;
}
mysql_free_result($result);

$result = mysql_query("SELECT * FROM orders, stores WHERE customerID='$customerID' AND orders.storeID=stores.storeID ORDER BY orderID DESC");
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
	$temp = array('orderID'=>$row['orderID'], 'storeID'=>$row['storeID'], 'store'=>$row['storeName'], 'tableID'=>$row['tableID'], 'time'=>$row['date'] . ' ' . $row['time'], 'total'=>$row['totalPrice'], 'payed'=>$row['payFlag'], 'fetched'=>$row['fetchFlag'], 'printed'=>$row['orderFlag']);
	array_push($arrlist, $temp);
}

$arr = array('list'=>$arrlist);
echo json_encode($arr);

mysql_free_result($result);
mysql_close($con);
?>
