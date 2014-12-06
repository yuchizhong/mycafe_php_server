<?php
$raw = file_get_contents("php://input");

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

//read from raw JSON
if (get_magic_quotes_gpc()) {
    $raw = stripslashes($raw);
}
$json = json_decode($raw, true);
$mall = $json["mall"];
$itemID = $json["itemID"];
$storeID = $json["storeID"];
$customer = $json["customer"];
$customerID = 0;
$error = "";
$ok_msg = "OK";
//get customerID by customer
$result = mysql_query("SELECT userID FROM user_login WHERE username='$customer'");
while ($row = mysql_fetch_array($result)) {
    $customerID = $row['userID'];
    break;
}
mysql_free_result($result);

if ($customerID == 0) {
    mysql_close($con);
    echo "ERROR_USER";
    exit(1);
}

//get time
$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds

mysql_query("START TRANSACTION");

if ($mall == "credit") {
    //check credit amound availability
    $creditsAvailable = 0;
    $creditsNeeded = -1;
    $chainID = -1;
    
    $q = "SELECT credit, chain_id FROM chain, stores, credit WHERE credit.user_id='$customerID' AND stores.storeID='$storeID' AND stores.chain_id=credit.chain_id";
    $result = mysql_query($q);
    while ($row = mysql_fetch_array($result)) {
        $creditsAvailable = intval($row['credit']);
        $chainID = intval($row['chain_id']);
        break;
    }
    mysql_free_result($result);
    
    $q = "SELECT credit FROM creditMall WHERE store_id='$storeID' AND item_id='$itemID'";
    $result = mysql_query($q);
    while ($row = mysql_fetch_array($result)) {
        $creditsNeeded = intval($row['credit']);
        break;
    }
    mysql_free_result($result);
    
    if ($creditsAvailable > $creditsNeeded && $creditsNeeded != -1) {
        //insert
        mysql_query("INSERT INTO creditTransaction VALUES (NULL, '$storeID', '$itemID', '$customerID', '$current_date', '$current_time', '1', '0')");
        
        //get transaction ID
        $result = mysql_query("SELECT MAX(transaction_id) FROM creditTransaction");
        $transacID = "";
        while ($row = mysql_fetch_array($result)) {
            $transacID = $row["MAX(transaction_id)"];
            break;
        }
        mysql_free_result($result);
        $ok_msg = "OK_" . $transacID;
        
        //update credits
        mysql_query("UPDATE credit SET credit=credit-'$creditsNeeded' WHERE chain_id='$chainID' AND user_id='$customerID'");
    } else {
        $error = "ERROR_ISF";
    }
} elseif ($mall == "cash") {
    /*
    //insert new transaction
    mysql_query("INSERT INTO orders VALUES (NULL, '$id', '$current_date', '$current_time', '$tableID', '$customerID', '0', '0', '0', '$total', '0')");
    //get id for payment
    $q = "SELECT MAX(orderID) FROM orders WHERE storeID='$id' AND customerID='$customerID'";
    $result = mysql_query($q);
    $transacID = "";
    while ($row = mysql_fetch_array($result)) {
       $transacID = $row["MAX(orderID)"];
       break;
    }
    mysql_free_result($result);
    $ok_msg = "OK_" . $transacID;
    */
    
    $error = "ERROR_MALL";
} elseif ($mall == "activity") {
    
    $error = "ERROR_MALL";
} elseif ($mall == "groupon") {
    
    $error = "ERROR_MALL";
} else {
    $error = "ERROR_MALL";
}



if ($error == "")
    mysql_query("COMMIT");
else
    mysql_query("ROLLBACK");

if ($error == "")
    echo $ok_msg;
else
    echo $error;

mysql_close($con);
?>
