<?php 
$username = $_GET["username"];
$birthyear = $_GET["birthyear"];
$birthmonth = $_GET["birthmonth"];
$gender = $_GET["gender"];

//pad left
$birthmonth = str_pad($birthmonth, 2, '0', STR_PAD_LEFT);

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
$q = "SELECT * FROM userinfo WHERE userID='$userID'";
$result = mysql_query($q);
while ($row = mysql_fetch_array($result)) {
	$found = true;
}
mysql_free_result($result); 

if ($found) { //not exist
	mysql_query("ROLLBACK");
	mysql_close($con);
	echo 'ERROR_FOUND';
	exit(1);
}

//found, update
mysql_query("INSERT INTO userinfo VALUES ('$userID', '$birthyear', '$birthmonth', '$gender')");
mysql_query("COMMIT");

echo 'OK';

mysql_close($con);
?>
