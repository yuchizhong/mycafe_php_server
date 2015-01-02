<?php
require(dirname(__FILE__) . '/encryption.php');

$input_data = json_decode(file_get_contents("php://input"), true);
if (empty($input_data['channel']) || empty($input_data['username'])) {
    echo 'ERROR_PARA';
    exit();
}

$mall = $input_data['mall'];
$amount = $input_data['amount'];

$channel = strtolower($input_data['channel']);
$storeID = intval($input_data['storeID']);
if ($mall == "cash")
    $storeID = $input_data['store_id'];
if ($storeID < 0 || $amount < 0) {
    echo 'ERROR_PARA_NEGATIVE';
    exit();
}
$uname = $input_data['username'];
$pw = $input_data['password'];
$orderID = $input_data['orderID'];
$pwenc = uhash($pw);

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$customerID = 0;
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$uname' AND password='$pwenc'");
while ($row = mysql_fetch_array($result)) {
    $customerID = intval($row['userID']);
    break;
}
mysql_free_result($result);

if ($customerID <= 0) {
    echo 'ERROR_USER';
    exit();
}

$storeName = "";
if ($storeID > 0) {
    $result = mysql_query("SELECT storeName FROM stores WHERE storeID='$storeID'");
    while ($row = mysql_fetch_array($result)) {
    	$storeName = $row['storeName'];
    }
    mysql_free_result($result);
}

$cli_ip = $_SERVER["REMOTE_ADDR"];

mysql_query("START TRANSACTION");
/*
if ($storeID > 0) {
    $amtCheck = 0.0;
    $result = mysql_query("SELECT * FROM orders WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0");
    while ($row = mysql_fetch_array($result)) {
    	$amtCheck += floatval($row['totalPrice']);
    }
    mysql_free_result($result);

    if ($amtCheck != floatval($amount)) {
        mysql_query("ROLLBACK");
        mysql_free_result($result);
        mysql_close($con);
        echo 'ERROR_AMOUNT';
        exit();
    }
}
*/

//insert new transaction to DB
$paymentID = 0;
$pingpp_no = "";
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

if ($mall == "normal" && $channel == "credit" && $storeID > 0) {
    //check and update remaining money
    $rem = 0;
    $chain_id = 0;
    $result = mysql_query("SELECT credit, credit.chain_id FROM stores, credit WHERE credit.user_id='$customerID' AND stores.storeID='$storeID' AND stores.chain_id=credit.chain_id");
    while ($row = mysql_fetch_array($result)) {
        $rem = intval($row['credit']);
        $chain_id = intval($row['chain_id']);
    }
    mysql_free_result($result);
    if ($rem < $amount) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_ISC_' . $rem;
        exit();
    }
    $rem = strval($rem - $amount);
    mysql_query("UPDATE credit SET credit='$rem' WHERE chain_id='$chain_id' AND user_id='$customerID'");

    //add payment
    mysql_query("INSERT INTO payment VALUES (NULL, '$mall', '', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'payed')");

    //get paymentID
    $result = mysql_query("SELECT MAX(paymentID) FROM payment");
    while ($row = mysql_fetch_array($result)) {
        $paymentID = intval($row["MAX(paymentID)"]);
        break;
    }
    mysql_free_result($result);

    //mark orders' payFlag and paymentID
    mysql_query("UPDATE orders SET payFlag=1, paymentID='$paymentID' WHERE storeID='$storeID' AND orderID='$orderID'");
    
    echo 'OK';
} else {
    echo 'ERROR_TYPE';
}

mysql_query("COMMIT");

mysql_close($con);
?>
