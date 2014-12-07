<?php
$input_data = json_decode(file_get_contents("php://input"), true);

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

mysql_query("START TRANSACTION");

if($input_data['object'] == 'charge') {    
    $pingpp_no = $input_data['order_no'];
    $cli_ip = $input_data['client_ip'];
    $amount = floatval($input_data['amount']) / 100;
    $channel = $input_data['channel'];
    
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
    
    if ($rowCount != 1) {
        mysql_query("ROLLBACK");
        mysql_close($con);
        echo 'fail';
        exit();
    }
    
    if ($mall == "normal") {
        mysql_query("UPDATE payment SET pay_status='payed' WHERE pingpp='$pingpp_no' AND client_ip='$cli_ip' AND channel='$channel' AND amount='$amount'");
        if ($storeID == 0) {
            //to purse
            mysql_query("UPDATE customers SET purse=purse+'$amount' WHERE customerID='$userID'");
        } else {
            //to store
            mysql_query("UPDATE orders SET payFlag=1 WHERE paymentID='$paymentID'");
        }
    } elseif ($mall == "cash") {
        mysql_query("UPDATE payment SET pay_status='payed' WHERE pingpp='$pingpp_no' AND client_ip='$cli_ip' AND channel='$channel' AND amount='$amount'");
        mysql_query("UPDATE cashTransaction SET status=1 WHERE paymentID='$paymentID'");
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
