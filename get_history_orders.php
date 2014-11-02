<?php
$customer = $_GET["ID"];
$store = $_GET["storeID"];

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

$result = mysql_query("SELECT * FROM dishes, orders, stores, orderDetails WHERE customerID='$customerID' AND orders.storeID='$store' AND payFlag=0 AND orders.storeID=stores.storeID AND dishes.storeID=orders.storeID AND dishes.dishID=orderDetails.dishID AND orderDetails.orderID=orders.orderID ORDER BY orders.orderID DESC, orderDetails.dishID ASC");
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
	$temp = array('tableID'=>$row['tableID'], 'dishID'=>$row['dishID'], 'dishName'=>$row['dishName'], 'price'=>$row['price'], 'quantity'=>$row['quantity'], 'orderID'=>$row['orderID'], 'store'=>$row['storeName'], 'time'=>$row['date'] . ' ' . $row['time'], 'total'=>$row['totalPrice'], 'payed'=>$row['payFlag'], 'fetched'=>$row['fetchFlag'], 'printed'=>$row['orderFlag']);
	array_push($arrlist, $temp);
}

$arr = array('list'=>$arrlist);
echo json_encode($arr);

mysql_free_result($result);
mysql_close($con);
?>
