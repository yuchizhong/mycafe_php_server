<?php 
$operation = $_GET["OPERATION"];
$deviceID = $_GET["UUID"];

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

if ($operation == "NEED_QQ") {
	$result = mysql_query("SELECT QQ FROM UUID_QQ WHERE UUID='$deviceID'");
	while ($row = mysql_fetch_array($result)) { 
		echo $row["QQ"];
		break;
	}
	mysql_free_result($result);
} elseif ($operation == "LOGIN") {
	$qqID = $_GET["QQ"];
	
	//ensure no dulplicated row
	$result = mysql_query("DELETE FROM UUID_QQ WHERE UUID='$deviceID'");
	mysql_free_result($result);
	
	$result = mysql_query("INSERT INTO UUID_QQ VALUES ($deviceID,$qqID)");
	mysql_free_result($result);
} elseif ($operation == "LOGOUT") {
	$result = mysql_query("DELETE FROM UUID_QQ WHERE UUID='$deviceID'");
	mysql_free_result($result);
}

mysql_close($con);
?>
