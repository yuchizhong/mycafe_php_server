<?php 
$province = $_GET["province"];
$city = $_GET["city"];
$district = $_GET["district"];
$lon = $_GET["longitude"];
$lat = $_GET["latitude"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

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

$q = "SELECT * FROM activity, stores WHERE storeName IS NOT NULL AND addr IS NOT NULL AND activity.date>='$current_date' AND activity.store_id=stores.storeID ORDER BY ACOS(SIN(('$lat' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) + COS(('$lat' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('$lon' * 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 ASC LIMIT 20";

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
$arr = array('list'=>$arrlist);
echo json_encode($arr);
mysql_free_result($result); 

mysql_close($con);
?>
