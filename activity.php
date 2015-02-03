<?php
/**
 * Created by PhpStorm.
 * User: Fajun Chen
 * Date: 1/16/15
 * Time: 8:00 PM
 */

$storeId = $_GET["storeId"];

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query("SELECT * FROM activity WHERE store_id='$storeId'");
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
    array_push($arrlist, $row);
}
$arr = array('list'=>$arrlist);
echo json_encode($arr);
mysql_free_result($result);

mysql_close($con);
?>
