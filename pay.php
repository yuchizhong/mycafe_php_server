<?php
require(dirname(__FILE__) . '/lib/PingPP.php');
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

$key = 'sk_test_rrzLCSebzbT8SKiH4GX9SWH8';
$appid = 'app_5qjfH0GKyPy5y5ar';

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

$customerID = 0;
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$uname'");
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

if ($mall == "activity" && $channel == "purse") {
    $ctransaction_id = $input_data['transaction_id'];
    $amount = $input_data['price'];
    $activity_id = $input_data['activity_id']; 
    
    //check and update remaining money
    $rem = 0.0;
    $result = mysql_query("SELECT purse FROM customers WHERE customerID='$customerID'");
    while ($row = mysql_fetch_array($result)) {
        $rem = floatval(dec($row['purse']));
    }
    mysql_free_result($result);
    if ($rem < $amount) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_ISF_' . $rem;
        exit();
    }
    $rem = enc(strval($rem - $amount));
    mysql_query("UPDATE customers SET purse='$rem' WHERE customerID='$customerID'");

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
    mysql_query("UPDATE activityTransaction SET status=1, paymentID='$paymentID', approve_status=1 WHERE transaction_id='$ctransaction_id'");
    //increment enrolled number
    mysql_query("UPDATE activity SET enrolled=enrolled+1 WHERE store_id='$storeID' AND activity_id='$activity_id'");
    echo "OK";
} elseif ($mall == "activity" && ($channel == "alipay" || $channel == "wx" || $channel == "upmp")) {
    $ctransaction_id = $input_data['transaction_id'];
    $amount = $input_data['price'];
    $activity_id = $input_data['activity_id'];
    
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
    mysql_query("INSERT INTO payment VALUES (NULL, '$mall', '$pingpp_no', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'unpayed')");

    $result = mysql_query("SELECT MAX(paymentID) FROM payment");
    while ($row = mysql_fetch_array($result)) {
        $paymentID = intval($row["MAX(paymentID)"]);
        break;
    }
    mysql_free_result($result);

    //update orders' paymentID, but not payFlag
    $result = mysql_query("UPDATE activityTransaction SET paymentID='$paymentID' WHERE transaction_id='$ctransaction_id'");
    mysql_free_result($result);

    $amt_in_cent = intval($amount * 100);
    PingPP::setApiKey($key);
    $ch = PingPP_Charge::create(
        array(
            "subject"   => "UniCafe活动报名费",
            "body"      => "共计￥" . $amount . "（店名：" . $storeName . ",账号：" . $uname . "）",
            "amount"    => $amt_in_cent,
            "order_no"  => $pingpp_no,
            "currency"  => "cny",
            "channel"   => $channel,
            "client_ip" => $_SERVER["REMOTE_ADDR"],
            "app"       => array("id" => $appid)
        )
    );
    echo $paymentID . ':' . $ch;
} elseif ($mall == "cash" && $channel == "purse") {
    $ctransaction_id = $input_data['transaction_id'];
    $amount = $input_data['price'];
    
    //check and update remaining money
    $rem = 0.0;
    $result = mysql_query("SELECT purse FROM customers WHERE customerID='$customerID'");
    while ($row = mysql_fetch_array($result)) {
    	$rem = floatval(dec($row['purse']));
    }
    mysql_free_result($result);
    if ($rem < $amount) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_ISF_' . $rem;
        exit();
    }
    $rem = enc(strval($rem - $amount));
    mysql_query("UPDATE customers SET purse='$rem' WHERE customerID='$customerID'");
    
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
    mysql_query("UPDATE cashTransaction SET status=1, paymentID='$paymentID' WHERE transaction_id='$ctransaction_id'");
    echo "OK";
} elseif ($mall == "cash" && ($channel == "alipay" || $channel == "wx" || $channel == "upmp")) {
    $ctransaction_id = $input_data['transaction_id'];
    $amount = $input_data['price'];
    
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
    
    //32位补齐
    if (strlen($pingpp_no) < 32) {
        $pingpp_no = str_pad($pingpp_no, 32, '0', STR_PAD_LEFT);
    }
    
    //add payment
    mysql_query("INSERT INTO payment VALUES (NULL, '$mall', '$pingpp_no', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'unpayed')");
    
    $result = mysql_query("SELECT MAX(paymentID) FROM payment");
    while ($row = mysql_fetch_array($result)) { 
        $paymentID = intval($row["MAX(paymentID)"]);
        break;
    }
    mysql_free_result($result);
    
    //update orders' paymentID, but not payFlag
    $result = mysql_query("UPDATE cashTransaction SET paymentID='$paymentID' WHERE transaction_id='$ctransaction_id'");
    mysql_free_result($result);
    
    $amt_in_cent = intval($amount * 100);
    PingPP::setApiKey($key);
    $ch = PingPP_Charge::create(
        array(
            "subject"   => "UniCafe商城消费",
            "body"      => "共计￥" . $amount . "（店名：" . $storeName . ",账号：" . $uname . "）",
            "amount"    => $amt_in_cent,
            "order_no"  => $pingpp_no,
            "currency"  => "cny",
            "channel"   => $channel,
            "client_ip" => $_SERVER["REMOTE_ADDR"],
            "app"       => array("id" => $appid)
        )
    );
    echo $paymentID . ':' . $ch;
} elseif (($mall == "normal" || $mall == "preorder") && $channel == "credit" && $storeID > 0) {
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
    if ($mall == "preorder") 
	mysql_query("UPDATE preorders SET payFlag=1, paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0 AND orderFlag=0");
    else
    	mysql_query("UPDATE orders SET payFlag=1, paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0 AND orderFlag=0");
    
    echo 'OK';
} elseif (($mall == "normal" || $mall == "preorder") && $channel == "purse" && $storeID > 0) {
    //check and update remaining money
    $rem = 0.0;
    $result = mysql_query("SELECT purse FROM customers WHERE customerID='$customerID'");
    while ($row = mysql_fetch_array($result)) {
	$rem = floatval(dec($row['purse']));
    }
    mysql_free_result($result);
    if ($rem < $amount) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'ERROR_ISF_' . $rem;
        exit();
    }
    $rem = enc(strval($rem - $amount));
    mysql_query("UPDATE customers SET purse='$rem' WHERE customerID='$customerID'");
    
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
    if ($mall == "preorder")
        mysql_query("UPDATE preorders SET payFlag=1, paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0 AND orderFlag=0");
    else
    	mysql_query("UPDATE orders SET payFlag=1, paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0 AND orderFlag=0");
    
    //add credit///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $totalCredit = 0;
    $sq = "SELECT totalCredit FROM orders WHERE paymentID='$paymentID'";
    if ($mall == "preorder")
	$sq = "SELECT totalCredit FROM preorders WHERE paymentID='$paymentID'";
    $result = mysql_query($sq);
    while ($row = mysql_fetch_array($result)) {
        $totalCredit += intval($row["totalCredit"]);
    }
    mysql_free_result($result);
    if ($totalCredit > 0) {
    //get chain_id by storeID
    $chainID = 0;
    $result = mysql_query("SELECT chain_id FROM stores WHERE storeID='$storeID'");
    while ($row = mysql_fetch_array($result)) {
        $chainID = intval($row["chain_id"]);
        break;
    }
    mysql_free_result($result);
    //insert into credit if not exist
    $creditExist = false;
    $result = mysql_query("SELECT * FROM credit WHERE user_id='$customerID' AND chain_id='$chainID'");
    while ($row = mysql_fetch_array($result)) {
        $creditExist = true;
        break;
    }
    mysql_free_result($result);
    if (!$creditExist) {
        mysql_query("INSERT INTO credit VALUES ('$chainID', '$customerID', '0', '0', '0')");
    }
    
    mysql_query("UPDATE credit SET credit=credit+'$totalCredit', accumulated_credit=accumulated_credit+'$totalCredit' WHERE user_id='$customerID' AND chain_id='$chainID'");
    
    //update member_level
    $memlv = 0;
    $acccredit = 0;
    $result = mysql_query("SELECT accumulated_credit FROM credit WHERE user_id='$customerID' AND chain_id='$chainID'");
    while ($row = mysql_fetch_array($result)) {
        $acccredit = intval($row["accumulated_credit"]);
        break;
    }
    mysql_free_result($result);
    
    //calculate level
    $result = mysql_query("SELECT MAX(member_level) FROM creditLevel WHERE chain_id='$chainID' AND level_credits<='$acccredit'");
    while ($row = mysql_fetch_array($result)) {
        $memlv = intval($row["MAX(member_level)"]);
        break;
    }
    mysql_free_result($result);

    mysql_query("UPDATE credit SET member_level='$memlv' WHERE user_id='$customerID' AND chain_id='$chainID'");
    }
    //add credit end///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    echo "OK";
} else if (($mall == "normal" || $mall == "preorder") && ($channel == "alipay" || $channel == "wx" || $channel == "upmp") && $storeID > 0) {
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
    mysql_query("INSERT INTO payment VALUES (NULL, '$mall', '$pingpp_no', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'unpayed')");
    
    $result = mysql_query("SELECT MAX(paymentID) FROM payment");
    while ($row = mysql_fetch_array($result)) { 
        $paymentID = intval($row["MAX(paymentID)"]);
        break;
    }
    mysql_free_result($result);
    
    //update orders' paymentID, but not payFlag
    if ($mall == "preorder")
        mysql_query("UPDATE preorders SET paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0 AND orderFlag=0");
    else
        mysql_query("UPDATE orders SET paymentID='$paymentID' WHERE customerID='$customerID' AND storeID='$storeID' AND payFlag=0 AND orderFlag=0");
    
    $amt_in_cent = intval($amount * 100);
    PingPP::setApiKey($key);
    $sbj = "UniCafe买单";
    if ($mall == "preorder")
	$sbj = "UniCafe预订";
    $ch = PingPP_Charge::create(
        array(
            "subject"   => $sbj,
            "body"      => "共计￥" . $amount . "（店名：" . $storeName . ",账号：" . $uname . "）",
            "amount"    => $amt_in_cent,
            "order_no"  => $pingpp_no,
            "currency"  => "cny",
            "channel"   => $channel,
            "client_ip" => $_SERVER["REMOTE_ADDR"],
            "app"       => array("id" => $appid)
        )
    );
    echo $paymentID . ':' . $ch;
} else if ($mall == "refill" && ($channel == "alipay" || $channel == "wx" || $channel == "upmp") && $storeID == 0) { // go to purse
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
    $result = mysql_query("INSERT INTO payment VALUES (NULL, '$mall', '$pingpp_no', '$cli_ip', '$channel', '$customerID', '$storeID', '$amount', '$current_date', '$current_time', 'unpayed')");
    mysql_free_result($result);
    
    $result = mysql_query("SELECT MAX(paymentID) FROM payment");
    while ($row = mysql_fetch_array($result)) {
        $paymentID = intval($row["MAX(paymentID)"]);
        break;
    }
    mysql_free_result($result);

    $amt_in_cent = intval($amount * 100);
    PingPP::setApiKey($key);
    $ch = PingPP_Charge::create(
        array(
            "subject"   => "UniCafe钱包充值",
            "body"      => "充值￥" . $amount . "（账号：" . $uname . "）",
            "amount"    => $amt_in_cent,
            "order_no"  => $pingpp_no,
            "currency"  => "cny",
            "channel"   => $channel,
            "client_ip" => $cli_ip,
            "app"       => array("id" => $appid)
        )
    );
    echo $paymentID . ':' . $ch;
} else {
    echo 'ERROR_TYPE';
}

mysql_query("COMMIT");

mysql_close($con);
