<?php
require(dirname(__FILE__) . '/push_func.php');

$msg = $_GET['message'];
$userID = $_GET['customerID'];
$user = $_GET['user'];
$sok = true;

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

if ($userID == NULL || $userID == "") {
    //get userID from user
    $result = mysql_query("SELECT userID FROM user_login WHERE username='$user'");
    while ($row = mysql_fetch_array($result)) { 
    	$userID = $row["userID"];
    	break;
    }
    mysql_free_result($result);
}

$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds
mysql_query("INSERT INTO notifications VALUES ('$userID', '$current_date', '$current_time', '$msg', '0')");

$result = mysql_query("SELECT push_token FROM UUID_user WHERE userID='$userID' AND platform=0 AND push_token IS NOT NULL AND push_token<>''");
while ($row = mysql_fetch_array($result)) {
    // Provide the Device Identifier (Ensure that the Identifier does not have spaces in it).
    // Replace this token with the token of the iOS device that is to receive the notification.
    $tToken = $row["push_token"];
    if(!send_push($msg, $tToken)) {
	$sok = false;
	break;
    }
}
if ($sok)
    echo 'OK';
else
    echo 'ERROR';
mysql_free_result($result);
mysql_close($con);
?>
