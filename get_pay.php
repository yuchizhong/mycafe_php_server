<?php 
$customer = $_GET["customerID"];
$store = $_GET["storeID"];

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");
$result = mysql_query("SELECT totalPrice FROM orders, user_login WHERE user_login.username='$customer' AND user_login.userID=orders.customerID AND storeID='$store' AND payFlag='0' AND orderFlag='0'");
$total = 0;
while ($row = mysql_fetch_array($result)) { 
	$total += floatval($row["totalPrice"]);
} 
echo $total;
mysql_free_result($result); 
mysql_close($con);
?>