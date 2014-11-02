<?php 
$level = $_GET["level"];

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

if ($level == "province") {
	$result = mysql_query("SELECT province FROM areas GROUP BY province");
	$arrlist = array();
	while ($row = mysql_fetch_array($result)) {
		array_push($arrlist, $row['province']);
	}
	$arr = array('list'=>$arrlist);
	if (!count($arrlist) == 0)
		echo json_encode($arr);
	mysql_free_result($result); 
} elseif ($level == "city") {
	$province = $_GET["province"];
	$result = mysql_query("SELECT city FROM areas WHERE province='$province' GROUP BY city");
	$arrlist = array();
	while ($row = mysql_fetch_array($result)) {
		array_push($arrlist, $row['city']);
	}
	$arr = array('list'=>$arrlist);
	if (!count($arrlist) == 0)
		echo json_encode($arr);
	mysql_free_result($result); 
} else { //district
	$province = $_GET["province"];
	$city = $_GET["city"];
	$result = mysql_query("SELECT district FROM areas WHERE province='$province' AND city='$city'");
	$arrlist = array();
	while ($row = mysql_fetch_array($result)) {
		array_push($arrlist, $row['district']);
	}
	$arr = array('list'=>$arrlist);
	if (!count($arrlist) == 0)
		echo json_encode($arr);
	mysql_free_result($result); 
}

mysql_close($con);
?>
