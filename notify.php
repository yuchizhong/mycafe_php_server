<?php
require(dirname(__FILE__) . '/encryption.php');
$input_data = json_decode(file_get_contents("php://input"), true);

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

mysql_query("START TRANSACTION");

if($input_data['object'] == 'charge') {    
    $pingpp_no = $input_data['order_no'];
    $cli_ip = $input_data['client_ip'];
    $amount = floatval($input_data['amount']) / 100;
    $channel = $input_data['channel'];
    $subject = $input_data['subject'];
    
    //search for the transaction
    $result = mysql_query("SELECT * FROM payment WHERE pingpp='$pingpp_no' AND client_ip='$cli_ip' AND channel='$channel' AND amount='$amount' AND pay_status='unpayed'");
    $rowCount = 0;
    $storeID = 0;
    $userID = 0;
    $paymentID = 0;
    $mall = "normal";
    while ($row = mysql_fetch_array($result)) {
    	$rowCount++;
        $paymentID = intval($row['paymentID']);
        $storeID = intval($row['storeID']);
        $userID = intval($row['userID']);
        $mall = $row['mall'];
    }
    mysql_free_result($result);
    $customerID = $userID;
    
    if ($rowCount != 1) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'fail';
        exit();
    }
    
    if ($mall == "normal" || $mall == "preorder" ||  $mall == "refill") {
        mysql_query("UPDATE payment SET pay_status='payed' WHERE pingpp='$pingpp_no' AND client_ip='$cli_ip' AND channel='$channel' AND amount='$amount'");
        if ($storeID == 0 && $mall == "refill") {
            //to purse
	    $pAmount = "";
	    $result = mysql_query("SELECT purse FROM customers WHERE customerID='$userID'");
    	while ($row = mysql_fetch_array($result)) {
		$pAmount = $row['purse'];
		break;
    	}
    	mysql_free_result($result);
	$pAmount = enc(strval(floatval(dec($pAmount)) + $amount));
            mysql_query("UPDATE customers SET purse='$pAmount' WHERE customerID='$userID'");
        } else {
            //to store
	    if ($mall == "preorder")
		mysql_query("UPDATE preorders SET payFlag=1 WHERE paymentID='$paymentID'");
	    else
        	mysql_query("UPDATE orders SET payFlag=1 WHERE paymentID='$paymentID'");
        }
        
        //add credit///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $totalCredit = 0;
        $result = mysql_query("SELECT totalCredit FROM orders WHERE paymentID='$paymentID'");
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
    } elseif ($mall == "cash") {
        mysql_query("UPDATE payment SET pay_status='payed' WHERE pingpp='$pingpp_no' AND client_ip='$cli_ip' AND channel='$channel' AND amount='$amount'");
        mysql_query("UPDATE cashTransaction SET status=1 WHERE paymentID='$paymentID'");
    } elseif ($mall == "activity") {
        mysql_query("UPDATE payment SET pay_status='payed' WHERE pingpp='$pingpp_no' AND client_ip='$cli_ip' AND channel='$channel' AND amount='$amount'");
        mysql_query("UPDATE activityTransaction SET status=1, approve_status=1 WHERE paymentID='$paymentID'");
        //increment enrolled number
        //get activity_id
	$activityID = 0;
	$result = mysql_query("SELECT activity_id FROM activityTransaction WHERE paymentID='$paymentID'");
        while ($row = mysql_fetch_array($result)) {
            $activityID = intval($row['activity_id']);
	    break;
        }
        mysql_free_result($result);
        mysql_query("UPDATE activity SET enrolled=enrolled+1 WHERE store_id='$storeID' AND activity_id='$activityID'");
    }
    
    echo 'success';
}
else if($input_data['object'] == 'refund') {
    echo 'success';
}
else {
    echo 'fail';
}

mysql_query("COMMIT");

mysql_close($con);
