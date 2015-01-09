<?php 
$username = $_GET["username"];
$storeID = $_GET["storeID"];

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

$mybirthyear = 0;
$mygender = 0;
$found = false;
$result = mysql_query("SELECT * FROM userinfo WHERE userID='$userID'");
$arrlistinfo = array();
while ($row = mysql_fetch_array($result)) {
    $found = true;
    $mygender = intval($row['gender']);
    $mybirthyear = intval($row['birthyear']);
    $temp = array();
    foreach ($row as $key => $value) {
        if (is_numeric($key))
                continue;
        $temp[$key] = $value;
    }
    array_push($arrlistinfo, $temp);
    break;
}
mysql_free_result($result);

/*
if (!$found) {
        echo "ERROR_USERINFO";
        mysql_close($con);
        exit(1);
}
 */

if ($found) {
//get time
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds
$current_year = date("Y");
$myage = intval($current_year) - $mybirthyear;

$gendercheck = ""; //AND (cafeWall.rest_gender=0 OR (cafeWall.rest_gender=1 AND 0='$mygender') OR (cafeWall.rest_gender=2 AND 1='$mygender'))
$agecheck = "";    //AND (cafeWall.rest_age_begin=0 OR cafeWall.rest_age_begin<='$myage') AND (cafeWall.rest_age_end=0 OR cafeWall.rest_age_end>='$myage')
$userNotSelf = ""; //AND cafeWall.post_userID<>'$userID'
$q = "SELECT * FROM cafeWall, dishes, customers, userinfo WHERE $gendercheck $agecheck $userNotSelf cafeWall.status=1 AND cafeWall.storeID='$storeID' AND dishes.storeID='$storeID' AND cafeWall.post_userID=userinfo.userID AND cafeWall.foodID=dishes.dishID AND cafeWall.post_userID=customers.customerID ORDER BY postID ASC";
$result = mysql_query($q);
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
    $temp = array();
    foreach ($row as $key => $value) {
        if (is_numeric($key))
                continue;
        $temp[$key] = $value;
    }
    array_push($arrlist, $temp);
}
}
$arr = array('list'=>$arrlist, 'userinfo'=>$arrlistinfo);
echo json_encode($arr);
mysql_free_result($result); 

mysql_close($con);
?>
