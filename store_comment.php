<?php
$storeID = $_GET["storeID"];
$username = $_GET["username"];
$rating = floatval($_GET["rating"]);
$comment = $_GET["comment"];

if ($rating == NULL || $rating == 0) {
	exit(1);
}
if ($comment == NULL) {
        $comment = "";
}

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

//get userID
$userID = 0;
$result = mysql_query("SELECT userID FROM user_login WHERE username='$username'");
while ($row = mysql_fetch_array($result)) {
    $userID = intval($row['userID']);
    break;
}
mysql_free_result($result);

if ($userID == 0) {
    echo 'ERROR_USER';
    mysql_close($con);
    exit(1);
}

mysql_query("START TRANSACTION");

//check evalution record
$found = false;
$result = mysql_query("SELECT * FROM storeEvaluation WHERE storeID='$storeID' AND userID='$userID'");
while ($row = mysql_fetch_array($result)) {
    $found = true;
    break;
}
mysql_free_result($result);

if ($found) {
    mysql_query("ROLLBACK");
    mysql_close($con);
    echo 'ERROR_DUPLICATE';
    exit(1);
}

//get customerID by customer
$result = mysql_query("SELECT numComments, rating FROM stores WHERE storeID='$storeID'");
$c_rating = 0.0;
$num_rating = 0;
while ($row = mysql_fetch_array($result)) {
    $c_rating = floatval($row['rating']);
    $num_rating = intval($row['numComments']);
    break;
}
mysql_free_result($result);

$new_rating = ($num_rating * $c_rating + $rating) / ($num_rating + 1);

$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

mysql_query("UPDATE stores SET numComments=numComments+1, rating='$new_rating' WHERE storeID='$storeID'");

mysql_query("INSERT INTO storeEvaluation VALUES ('$storeID', '$userID', '$current_date', '$current_time', '$rating', '$comment')");

mysql_query("COMMIT");

echo 'OK';

mysql_close($con);
?>
