<?php
$customer = $_GET["user"];

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

$result = mysql_query("SELECT body FROM notifications WHERE userID='$customerID' AND status=0");
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
        array_push($arrlist, $row['body']);
}

$arr = array('list'=>$arrlist);
echo json_encode($arr);

mysql_query("UPDATE notifications SET status=1 WHERE userID='$customerID' AND status=0");

mysql_free_result($result);
mysql_close($con);
?>
