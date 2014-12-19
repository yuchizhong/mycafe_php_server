<?php
$customer = $_GET["ID"];
$store = $_GET["storeID"];
$orderID = $_GET["orderID"];

$con = mysql_connect("localhost", "root", "Unicoffee168");
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

$result = mysql_query("SELECT * FROM dishes, preorders, stores, preorderDetails WHERE customerID='$customerID' AND preorders.storeID='$store' AND preorders.storeID=stores.storeID AND dishes.storeID=preorders.storeID AND dishes.dishID=preorderDetails.dishID AND preorders.preorderID='$orderID' AND preorderDetails.preorderID=preorders.preorderID ORDER BY preorders.preorderID DESC, preorderDetails.dishID ASC");
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
	$temp = array('dishID'=>$row['dishID'], 'dishName'=>$row['dishName'], 'price'=>$row['price'], 'quantity'=>$row['quantity'], 'orderID'=>$row['preorderID'], 'store'=>$row['storeName'], 'time'=>$row['date'] . ' ' . $row['time'], 'total'=>$row['totalPrice'], 'payed'=>$row['payFlag'], 'fetched'=>$row['fetchFlag'], 'printed'=>$row['orderFlag']);
	array_push($arrlist, $temp);
}

$arr = array('list'=>$arrlist);
echo json_encode($arr);

mysql_free_result($result);
mysql_close($con);
?>
