<?php
$query = file_get_contents("php://input");

if (substr($query, 0, 6) != "SELECT" || strstr($query, ";") != false) {
    echo "ERROR";
    exit(1);
}

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

$result = mysql_query($query);
$arrlist = array();
while ($row = mysql_fetch_array($result)) {
    $temp = array();
    foreach ($row as $key => $value) {
	$temp[$key] = $value;
    }
    array_push($arrlist, $temp);
}

$arr = array('list'=>$arrlist);
echo json_encode($arr);

mysql_free_result($result);
mysql_close($con);
?>
