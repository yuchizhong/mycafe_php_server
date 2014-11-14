<?php
require_once(dirname(__FILE__) . '/lib/PingPP.php');

$input_data = json_decode(file_get_contents("php://input"), true);
if (empty($input_data['channel']) || empty($input_data['amount']) || empty($input_data['username'])) {
    echo 'ERROR_PARA';
    exit();
}

$amount = $input_data['amount'];

$channel = strtolower($input_data['channel']);
$storeID = intval($input_data['storeID']);
if ($storeID < 0 || $amount < 0) {
    echo 'ERROR_PARA_NEGATIVE';
    exit();
}
$uname = $input_data['username'];

$key = 'sk_test_rrzLCSebzbT8SKiH4GX9SWH8';
$appid = 'app_5qjfH0GKyPy5y5ar';

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

$customerID = 0;
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$uname'");
while ($row = mysql_fetch_array($result)) {
    $customerID = $row['userID'];
    break;
}
mysql_free_result($result);

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

//insert new transaction to DB
$paymentID = 0;
$pingpp_no = "";
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds
if ($channel == "purse" && $storeID > 0) {
    //check and update remaining money
    $rem = 0.0;
    $result = mysql_query("SELECT purse FROM customers WHERE customerID='$customerID'");
    while ($row = mysql_fetch_array($result)) {
    	$rem = floatval($row['purse']);
    }
    mysql_free_result($result);
    if ($rem < $amount) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_ISF_' . $rem;
        exit();
    }
    mysql_query("UPDATE customers SET purse=purse-'$amount' WHERE customerID='$customerID'");
    
    //add payment
    mysql_query("INSERT INTO payment VALUES (NULL, '', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'payed')");
    
    //get paymentID
    $result = mysql_query("SELECT MAX(paymentID) FROM payment");
    while ($row = mysql_fetch_array($result)) { 
        $paymentID = intval($row["MAX(paymentID)"]);
        break;
    }
    mysql_free_result($result);
    
    //mark orders' payFlag and paymentID
    mysql_query("UPDATE orders SET payFlag=1, paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0");
    echo "OK";
} /*else if (($channel == "alipay" || $channel == "wx" || $channel == "upmp") && $storeID > 0) {
    if (floatval($amount) <= 0) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_ZERO';
        exit();
    }
    $pingpp_max = "";
    $result = mysql_query("SELECT MAX(pingpp) FROM payment");
    while ($row = mysql_fetch_array($result)) { 
        $pingpp_max = strval($row["MAX(pingpp)"]);
        break;
    }
    mysql_free_result($result);
    
    $pingpp_max_no = base_convert($pingpp_max, 36, 10) + 1;
    $pingpp_no = base_convert(strval($pingpp_max_no), 10, 36);
    
    //8位补齐
    if (strlen($pingpp_no) < 32) {
        $pingpp_no = str_pad($pingpp_no, 32, '0', STR_PAD_LEFT);
    }
    
    //add payment
    mysql_query("INSERT INTO payment VALUES (NULL, '$pingpp_no', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'unpayed')");
    
    //update orders' paymentID, but not payFlag
    $result = mysql_query("UPDATE orders SET paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0");
    mysql_free_result($result);
    
    $amt_in_cent = intval($amount * 100);
    PingPP::setApiKey($key);
    $ch = PingPP_Charge::create(
        array(
            "subject"   => "爱易点买单",
            "body"      => "共计￥" . $amount . "（店名：" . $storeName . ",账号：" . $uname . "）",
            "amount"    => $amt_in_cent,
            "order_no"  => $pingpp_no,
            "currency"  => "cny",
            "channel"   => $channel,
            "client_ip" => $_SERVER["REMOTE_ADDR"],
            "app"       => array("id" => $appid)
    )
    );
    echo $ch;
} */else if (($channel == "alipay" || $channel == "wx" || $channel == "upmp") && $storeID == 0) { // go to purse
    if (floatval($amount) <= 0) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_ZERO';
        exit();
    }
    $pingpp_max = "";
    $result = mysql_query("SELECT MAX(pingpp) FROM payment");
    while ($row = mysql_fetch_array($result)) { 
        $pingpp_max = strval($row["MAX(pingpp)"]);
        break;
    }
    mysql_free_result($result);
    
    $pingpp_max_no = base_convert($pingpp_max, 36, 10) + 1;
    $pingpp_no = base_convert(strval($pingpp_max_no), 10, 36);
    
    //8位补齐
    if (strlen($pingpp_no) < 32) {
        $pingpp_no = str_pad($pingpp_no, 32, '0', STR_PAD_LEFT);
    }
            
    //add payment
    $result = mysql_query("INSERT INTO payment VALUES (NULL, '$pingpp_no', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'unpayed')");
    mysql_free_result($result);
    
    $amt_in_cent = intval($amount * 100);
    PingPP::setApiKey($key);
    $ch = PingPP_Charge::create(
        array(
            "subject"   => "爱易点钱包充值",
            "body"      => "充值￥" . $amount . "（账号：" . $uname . "）",
            "amount"    => $amt_in_cent,
            "order_no"  => $pingpp_no,
            "currency"  => "cny",
            "channel"   => $channel,
            "client_ip" => $cli_ip,
            "app"       => array("id" => $appid)
    )
    );
    echo $ch;
} else {
    echo 'ERROR_TYPE';
}

mysql_query("COMMIT");

mysql_close($con);
