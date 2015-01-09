<?php 
$username = $_GET["username"];
$storeID = $_GET["storeID"];
$postID = $_GET["postID"];

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$userID = "";

//get userID from user
$result = mysql_query("SELECT userID FROM user_login WHERE username='$username'");
while ($row = mysql_fetch_array($result)) {
    $userID = $row["userID"];
    break;
}
mysql_free_result($result);

if ($userID == NULL)
        $userID = "";

if ($userID == "") {
	echo "ERROR_USER";
	mysql_close($con);
	exit(1);
}

//get time
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

mysql_query("START TRANSACTION");

$found = false;
$sameUser = false;
$q = "SELECT * FROM cafeWall WHERE status=1 AND storeID='$storeID' AND postID='$postID'";
$result = mysql_query($q);
while ($row = mysql_fetch_array($result)) {
	if (intval($row['post_userID']) == $userID) {
		$sameUser = true;
		break;
	}
	$found = true;
}
mysql_free_result($result); 

if ($sameUser) { //same user as post user
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_SAME_USER';
        exit(1);
}

if (!$found) { //not exist
	mysql_query("ROLLBACK");
	mysql_close($con);
	echo 'ERROR_NOT_FOUND';
	exit(1);
}

//found, update
mysql_query("UPDATE cafeWall SET status=2, consume_userID='$userID', consume_date='$current_date', consume_time='$current_time' WHERE status=1 AND storeID='$storeID' AND postID='$postID'");
mysql_query("COMMIT");

echo 'OK';

mysql_close($con);
?>
