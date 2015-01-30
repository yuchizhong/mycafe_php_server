<?php
require(dirname(__FILE__) . '/encryption.php');
require(dirname(__FILE__) . '/sms_func.php');

function seed() {
	list($msec, $sec) = explode(' ', microtime());
	return (float) $sec;
}

$operation = $_GET["OPERATION"];
$deviceID = $_GET["UUID"];
$platform = $_GET["platform"];
if ($platform == NULL || $platform == "") {
	$platform = "0";
}

$con = mysql_connect("localhost", "root", "Unicoffee168");
mysql_select_db("order");
mysql_query("set names utf8");

if ($operation == "CHANGE_NAME") {
	$ID = $_GET["ID"];
        $newName = $_GET["name"];
	
	$result = mysql_query("SELECT userID FROM user_login WHERE username='$ID'");
        while ($row = mysql_fetch_array($result)) {
                $ID = $row["userID"];
                break;
        }
        mysql_free_result($result);
	
	mysql_query("UPDATE customers SET customerName='$newName' WHERE customerID='$ID'");
	echo "OK";
} else if ($operation == "get_verf_code") {
	$ID = $_GET["ID"];
	$result = mysql_query("DELETE FROM verification_code WHERE tel='$ID'");
        mysql_free_result($result);
	
	/*
        $result = mysql_query("SELECT userID FROM user_login WHERE username='$ID'");
        $haveOne = 0;
        while ($row = mysql_fetch_array($result)) { 
                $haveOne = 1;
                break;
        }
        mysql_free_result($result);
        if ($haveOne == 1) {
                mysql_close($con);
                echo 'ERROR'; //user exists
                return;
        }
	 */
	
	srand(seed());
	$randed = rand(1234,9876);
	$newCode = "$randed";
	$result = mysql_query("INSERT INTO verification_code VALUES ($ID, $newCode)");
        mysql_free_result($result);
	
	//sms
	mySendSMS($ID, '您的注册验证码为' . $randed . '，请妥善保管并尽快输入。');
	
	echo 'SENT_SMS';
} elseif ($operation == "REGISTER") {
	$ID = $_GET["ID"];
	$pass = $_GET["pass"];
	$pass = uhash($pass);
	$verf = $_GET["verification"];
	$pushT = $_GET['push_token'];
	if ($pushT == NULL)
		$pushT = "";
	$userID = 0;
	$result = mysql_query("SELECT userID FROM user_login WHERE username='$ID'");
	$haveOne = 0;
	while ($row = mysql_fetch_array($result)) { 
		$haveOne = 1;
		break;
	}
	mysql_free_result($result);
	if ($haveOne == 1) {
		mysql_close($con);
		echo 'ERROR';
		return;
	}

	$result = mysql_query("SELECT code FROM verification_code WHERE tel='$ID'");
        $code = "";
        while ($row = mysql_fetch_array($result)) { 
                $code = $row['code'];
                break;
        }
        mysql_free_result($result);
	if ($code == NULL || $code == "" || $code != $verf) {
                mysql_close($con);
                echo 'ERROR_CODE';
                return;
        }
	
	$result = mysql_query("DELETE FROM verification_code WHERE tel='$ID'");
        mysql_free_result($result);
	
	mysql_query("START TRANSACTION");

	//insert new user
	$result = mysql_query("INSERT INTO customers VALUES (NULL, '$ID', '', '$ID', '', '827d8a45brQqjRAA')");
	mysql_free_result($result);
	
	//get userID
	$result = mysql_query("SELECT MAX(customerID) FROM customers");
	while ($row = mysql_fetch_array($result)) { 
		$userID = $row["MAX(customerID)"];
		break;
	}
	mysql_free_result($result);
	
	$result = mysql_query("INSERT INTO user_login VALUES ('$userID', '$ID', '$pass')");
	mysql_free_result($result);
	
	//login
	$result = mysql_query("DELETE FROM UUID_user WHERE UUID='$deviceID' AND platform='$platform'");
	mysql_free_result($result);
	
	$result = mysql_query("INSERT INTO UUID_user VALUES ('$deviceID', '$userID', '$pushT', '$platform')");
	mysql_free_result($result);
	
	mysql_query("COMMIT");
	
	echo 'OK';
} elseif ($operation == "RESET_PW") {
        $ID = $_GET["ID"];
        $pass = $_GET["pass"];
	$pass = uhash($pass);
        $verf = $_GET["verification"];
        $pushT = $_GET['push_token'];
        if ($pushT == NULL)
                $pushT = "";
        $userID = 0;
        $result = mysql_query("SELECT userID FROM user_login WHERE username='$ID'");
        $haveOne = 0;
        while ($row = mysql_fetch_array($result)) {
		$userID = $row['userID'];
                $haveOne = 1;
                break;
        }
        mysql_free_result($result);
        if ($haveOne == 0) {
                mysql_close($con);
                echo 'ERROR';
                return;
        }

        $result = mysql_query("SELECT code FROM verification_code WHERE tel='$ID'");
        $code = "";
        while ($row = mysql_fetch_array($result)) {
                $code = $row['code'];
                break;
        }
        mysql_free_result($result);
        if ($code == NULL || $code == "" || $code != $verf) {
                mysql_close($con);
                echo 'ERROR_CODE';
                return;
        }

        $result = mysql_query("DELETE FROM verification_code WHERE tel='$ID'");
        mysql_free_result($result);

        mysql_query("START TRANSACTION");
	
        //update password
        $result = mysql_query("UPDATE user_login SET password='$pass' WHERE userID='$userID'");
        mysql_free_result($result);
        
	//login
        $result = mysql_query("DELETE FROM UUID_user WHERE UUID='$deviceID' AND platform='$platform'");
        mysql_free_result($result);

        $result = mysql_query("INSERT INTO UUID_user VALUES ('$deviceID', '$userID', '$pushT', '$platform')");
        mysql_free_result($result);
	
	$usernick = "";
	$result = mysql_query("SELECT customerName FROM customers WHERE customerID='$userID'");
        while ($row = mysql_fetch_array($result)) {
		$usernick = $row['customerName'];
                break;
        }
        mysql_free_result($result);
	
        mysql_query("COMMIT");

        echo 'OK_' . $usernick;
} elseif ($operation == "GET_ID") {
	$username = "";
	$pushT = $_GET['push_token'];
	if ($pushT == NULL)
		$pushT = "";
	$result = mysql_query("SELECT username FROM UUID_user, user_login WHERE UUID='$deviceID' AND UUID_user.userID=user_login.userID");
	while ($row = mysql_fetch_array($result)) { 
		$username = $row["username"];
		if ($pushT != "") {
			mysql_query("UPDATE UUID_user SET push_token='$pushT' WHERE UUID='$deviceID AND platform='$platform'");
		}
		break;
	}
	if ($username == NULL || $username == '')
		echo "NOF";
	else
		echo $username;
	mysql_free_result($result);
} elseif ($operation == "GET_NICKNAME") {
	$ID = $_GET["ID"];
	$username = "";
        $result = mysql_query("SELECT customerName FROM customers, user_login WHERE username='$ID' AND customers.customerID=user_login.userID");
        while ($row = mysql_fetch_array($result)) {
                $username = $row["customerName"];
                break;
        }
        echo $username;
        mysql_free_result($result);
}  elseif ($operation == "LOGIN") {
	$ID = $_GET["ID"];
	$pass = $_GET["pass"];
	$pass = uhash($pass);
	$pushT = $_GET['push_token'];
	if ($pushT == NULL)
		$pushT = "";
	$userID = 0;
	$haveOne = 0;
	
	$result = mysql_query("SELECT userID FROM user_login WHERE username='$ID' AND password='$pass'");
	while ($row = mysql_fetch_array($result)) { 
		$userID = $row["userID"];
		$haveOne = 1;
		break;
	}
	mysql_free_result($result);
	
	if ($haveOne == 0) {
		mysql_close($con);
		echo 'ERROR';
		return;
	}
	
	//ensure no dulplicated row
	$result = mysql_query("DELETE FROM UUID_user WHERE UUID='$deviceID' AND platform='$platform'");
	mysql_free_result($result);
	
	$result = mysql_query("INSERT INTO UUID_user VALUES ('$deviceID', '$userID', '$pushT', '$platform')");
	mysql_free_result($result);
	
	//get nickname
	$nickname = "";
        $result = mysql_query("SELECT customerName FROM customers WHERE customerID='$userID'");
        while ($row = mysql_fetch_array($result)) {
                $nickname = $row["customerName"];
                break;
        }
        echo "OK_" . $nickname;
} elseif ($operation == "LOGOUT") {
	$result = mysql_query("DELETE FROM UUID_user WHERE UUID='$deviceID' AND platform='$platform'");
	mysql_free_result($result);
	echo 'OK';
}

mysql_close($con);
?>
