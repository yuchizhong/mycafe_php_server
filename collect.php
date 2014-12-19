<?php
$op = $_GET['OPERATION'];
$storeID = $_GET['storeID'];
$user = $_GET['username'];

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$userID = "";

//get userID from user
$result = mysql_query("SELECT userID FROM user_login WHERE username='$user'");
while ($row = mysql_fetch_array($result)) { 
    $userID = $row["userID"];
    break;
}
mysql_free_result($result);

if ($userID == NULL || $userID == "") {
    echo 'ERROR_USER';
    exit(1);
}

//check if record exists
mysql_query("START TRANSACTION");
$ext = false;
$result = mysql_query("SELECT * FROM collect WHERE store_id='$storeID' AND user_id='$userID'");
while ($row = mysql_fetch_array($result)) {     
    $ext = true;
    break;
}
mysql_free_result($result);

if ($op == "COLLECT") {
	if (!$ext) {
		mysql_query("INSERT INTO collect VALUES ('$storeID','$userID')");
	}
	echo "OK";
} elseif ($op == "UNCOLLECT") {
	if ($ext) {
		mysql_query("DELETE FROM collect WHERE store_id='$storeID' AND user_id='$userID'");
	}
	echo "OK";
} else {
    echo "ERROR_TYPE";
}

mysql_query("COMMIT");

mysql_free_result($result);
mysql_close($con);
?>
