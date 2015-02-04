<?php
require(dirname(__FILE__) . '/encryption.php');
$username = $_GET['username'];
$storeID = $_GET['storeID'];

$credit = 0;

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query("SELECT credit FROM stores, credit, user_login WHERE user_login.username='$username' AND credit.user_id=user_login.userID AND stores.storeID='$storeID' AND stores.chain_id=credit.chain_id");
while ($row = mysql_fetch_array($result)) {
	$credit = intval($row['credit']);
	break;
}

mysql_free_result($result);
mysql_close($con);

echo $credit;
?>