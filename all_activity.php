<?php 
$lon = $_GET["longitude"];
$lat = $_GET["latitude"];
$username = $_GET["username"];
$store_id = $_GET["app_store_id"];
$nrows = $_GET["numRecords"];
if ($nrows == NULL || $nrows == "") {
    $nrows = 20;
}

if ($store_id == NULL)
	$store_id = 0;

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

//$q = "SELECT * FROM all_stores WHERE province='$province' AND city='$city' AND district='$district' ORDER BY matchingStore DESC";
//if ($province === 'ALL')
//	$q = "SELECT * FROM stores ORDER BY supportFlag DESC, storeID DESC";
//elseif ($city === 'ALL')
//	$q = "SELECT * FROM all_stores WHERE province='$province' ORDER BY matchingStore DESC";
//elseif ($district === 'ALL')
//	$q = "SELECT * FROM all_stores WHERE province='$province' AND city='$city' ORDER BY matchingStore DESC";

//get time
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

$q = "SELECT *, activity.description AS ac_desp FROM activity, stores WHERE publish_status=1 AND storeName IS NOT NULL AND addr IS NOT NULL AND date IS NOT NULL AND deadline_date IS NOT NULL AND activity.date>='$current_date' AND activity.store_id=stores.storeID ORDER BY ACOS(SIN(('$lat' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) + COS(('$lat' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('$lon' * 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 ASC LIMIT $nrows";

if ($store_id > 0)
	$q = "SELECT *, activity.description AS ac_desp FROM activity, stores WHERE activity.store_id='$store_id' AND publish_status=1 AND storeName IS NOT NULL AND addr IS NOT NULL AND date IS NOT NULL AND deadline_date IS NOT NULL AND activity.date>='$current_date' AND activity.store_id=stores.storeID ORDER BY activity.activity_id DESC LIMIT $nrows";

$result = mysql_query($q);
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
    $temp = array();
    //user has enrolled?
    $userEnrolled = '0'; 
    $sid = $row['store_id'];
    $aid = $row['activity_id'];
    $result2 = mysql_query("SELECT * FROM activityTransaction WHERE user_id='$userID' AND store_id='$sid' AND activity_id='$aid' AND status>0");
    while ($row2 = mysql_fetch_array($result2)) {
            $userEnrolled = '1';
            break;
    }
    mysql_free_result($result2);
    $temp['userEnrolled'] = $userEnrolled;
    
    /*
    $collected = '0';
    $result2 = mysql_query("SELECT * FROM collect WHERE user_id='$userID' AND store_id='$sid'");
    while ($row2 = mysql_fetch_array($result2)) {
            $collected = '1';
            break;
    }
    mysql_free_result($result2);
    $temp['collected'] = $collected;
     */
    
    $scoreCost = 0;
    if ($row["bScoreEnroll"] == 1)
	$scoreCost = $row["enrolledScores"];
    elseif ($row["bDonateScore"] == 1)
	$scoreCost = -intval($row["donateScores"]);
    $temp["creditPrice"] = strval($scoreCost);
 
    foreach ($row as $key => $value) {
        if (is_numeric($key))
                continue;
        $temp[$key] = $value;
    }
    array_push($arrlist, $temp);
}
$arr = array('list'=>$arrlist);
echo json_encode($arr);
mysql_free_result($result); 

mysql_close($con);
?>
