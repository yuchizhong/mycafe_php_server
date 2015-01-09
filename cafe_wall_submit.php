<?php 
$username = $_GET["username"];
$storeID = $_GET["storeID"];
$foodID = $_GET["foodID"];
$lowerAge = $_GET["lowerAge"];
$upperAge = $_GET["upperAge"];
$gender = $_GET["gender"];
$message = $_GET["message"];

if ($msg == NULL)
	$msg = "";

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

$maxPostID = 0;
$q = "SELECT MAX(postID) FROM cafeWall WHERE storeID='$storeID'";
$result = mysql_query($q);
while ($row = mysql_fetch_array($result)) {
	$maxPostID = intval($row['MAX(postID)']);
}
mysql_free_result($result); 

$maxPostID++;

//insert
mysql_query("INSERT INTO cafeWall VALUES ('$storeID', '$maxPostID', '$userID', '0', '$foodID', '$current_date', '$current_time', '', '', '1', '0', '$gender', '$lowerAge', '$upperAge', '$message')");
mysql_query("COMMIT");

echo 'OK_' . $maxPostID;

mysql_close($con);
?>
