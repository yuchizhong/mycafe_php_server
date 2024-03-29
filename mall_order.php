<?php
$raw = file_get_contents("php://input");

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

//read from raw JSON
if (get_magic_quotes_gpc()) {
    $raw = stripslashes($raw);
}
$json = json_decode($raw, true);
$mall = $json["mall"];
$itemID = $json["item_id"];
$activityID = $json["activity_id"];
$storeID = $json["store_id"];
$customer = $json["user_name"];
$customerID = 0;
$price = floatval($json["price"]);
$platform = $json["platform"];
if ($platform == NULL || $platform == "") {
	$platform = "0";
}
$tableID = $json["tableID"];
if ($tableID == NULL || $tableID == "") {
        $tableID = "0";
}
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
    
    $q = "SELECT credit, chain.chain_id FROM chain, stores, credit WHERE credit.user_id='$customerID' AND stores.storeID='$storeID' AND stores.chain_id=credit.chain_id";
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
        mysql_query("INSERT INTO creditTransaction VALUES (NULL, '$storeID', '$itemID', '$customerID', '$current_date', '$current_time', '1', '0', NULL)");
        
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
    //insert new transaction
    mysql_query("INSERT INTO cashTransaction VALUES (NULL, '$storeID', '$itemID', '$customerID', '$current_date', '$current_time', '0', '0', '0', '$tableID', '$platform')");
    //get id for payment
    $q = "SELECT MAX(transaction_id) FROM cashTransaction";
    $result = mysql_query($q);
    $transacID = "";
    while ($row = mysql_fetch_array($result)) {
       $transacID = $row["MAX(transaction_id)"];
       break;
    }
    mysql_free_result($result);
    $ok_msg = "OK_" . $transacID;
} elseif ($mall == "activity") {
    //check people
    $max = 0;
    $enrolled = 0;
    $ac_date = "";
    $ac_time = "";
    $ac_price = 0.0;
    $alreadyIN = false;
    $result = mysql_query("SELECT * FROM activity WHERE store_id='$storeID' AND activity_id='$activityID'");
    while ($row = mysql_fetch_array($result)) {
        $max = intval($row['max']);
	$enrolled = intval($row['enrolled']);
	$ac_date = $row['deadline_date'];
    	$ac_time = $row['deadline_time'];
   	$ac_price = floatval($row['price']);
	break;
    }
    mysql_free_result($result);

   $result = mysql_query("SELECT * FROM activityTransaction WHERE store_id='$storeID' AND activity_id='$activityID' AND user_id='$customerID' AND status=1");
    while ($row = mysql_fetch_array($result)) {
        $alreadyIN = true;
	break;
    }
    mysql_free_result($result);

    if ($ac_price != $price) {
	$error = "ERROR_PRICE";
    } elseif ($max != 0 && $enrolled >= $max) {
	$error = "ERROR_FULL";
    } elseif ($alreadyIN) {
        $error = "ERROR_ALREADY_IN";
    } elseif ($ac_date < $current_date) {
    	//check date and time
	$error = "ERROR_TIME";
    } elseif ($ac_date == $current_date && $ac_time < $current_time) {
        //check date and time
        $error = "ERROR_TIME";
    } else {
	//write to DB, if free, write status to 1 and inc enrolled
	if ($ac_price == 0.0) {
	    //insert new transaction
	    mysql_query("INSERT INTO activityTransaction VALUES (NULL, '$storeID', '$activityID', '$customerID', '$current_date', '$current_time', '1', '0', '0', '0')");
	    //increment enrolled number
            mysql_query("UPDATE activity SET enrolled=enrolled+1 WHERE store_id='$storeID' AND activity_id='$activityID'");
  	} else {
	    mysql_query("INSERT INTO activityTransaction VALUES (NULL, '$storeID', '$activityID', '$customerID', '$current_date', '$current_time', '0', '0', '0', '0')");
	}
   	 //get id for payment
   	 $q = "SELECT MAX(transaction_id) FROM activityTransaction";
   	 $result = mysql_query($q);
   	 $transacID = "";
   	 while ($row = mysql_fetch_array($result)) {
      		 $transacID = $row["MAX(transaction_id)"];
       	         break;
    	}
    	mysql_free_result($result);
    	$ok_msg = "OK_" . $transacID;
    }
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
