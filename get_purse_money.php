<?php
require(dirname(__FILE__) . '/encryption.php');
$username = $_GET['username'];
$money = 0.0;
$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query("SELECT purse FROM customers, user_login WHERE user_login.username='$username' AND customers.customerID=user_login.userID");
while ($row = mysql_fetch_array($result)) {
	$money = dec($row['purse']);
	break;
}

mysql_free_result($result);
mysql_close($con);

echo $money;
?>
