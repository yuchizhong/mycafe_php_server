<?php
$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query("SELECT province FROM areas GROUP BY province ORDER BY districtID");
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
	$province = $row['province'];
	$arrlist2 = array();
	$result2 = mysql_query("SELECT city FROM areas WHERE province='$province' GROUP BY city ORDER BY districtID");
	
	while ($row2 = mysql_fetch_array($result2)) {
		$city = $row2['city'];
		$arrlist3 = array();
		$result3 = mysql_query("SELECT district FROM areas WHERE province='$province' AND city='$city' ORDER BY districtID");
		while ($row3 = mysql_fetch_array($result3)) {
			$district = $row3['district'];
			array_push($arrlist3, $district);
		}
		mysql_free_result($result3);
		$districtList = array('city'=>$city, 'list'=>$arrlist3);
		array_push($arrlist2, $districtList);
	}
	
	$arr2 = array('province'=>$province, 'list'=>$arrlist2);
	mysql_free_result($result2);
	
	array_push($arrlist, $arr2);
}
$arr = array('list'=>$arrlist);
if (!count($arrlist) == 0)
	echo json_encode($arr);
mysql_free_result($result); 

mysql_close($con);
?>
