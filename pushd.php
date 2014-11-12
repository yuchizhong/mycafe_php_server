<?php
$msg = $_GET['message'];
$userID = $_GET['customerID'];
$user = $_GET['user'];
$sok = true;

$con = mysql_connect("localhost", "root", "123456");
mysql_select_db("order");
mysql_query("set names utf8");

if ($userID == NULL || $userID == "") {
    //get userID from user
    $result = mysql_query("SELECT userID FROM user_login WHERE username='$user'");
    while ($row = mysql_fetch_array($result)) { 
    	$userID = $row["userID"];
    	break;
    }
    mysql_free_result($result);
}

$current_date = date("Ymd");
$current_time = date("H:i"); //add s if need seconds
mysql_query("INSERT INTO notifications VALUES ('$userID', '$current_date', '$current_time', '$msg', '0')");

$result = mysql_query("SELECT push_token FROM UUID_user WHERE userID='$userID'");
while ($row = mysql_fetch_array($result)) {
    // Provide the Device Identifier (Ensure that the Identifier does not have spaces in it).
    // Replace this token with the token of the iOS device that is to receive the notification.
    $tToken = $row["push_token"];
    if ($tToken == NULL || $tToken == "")
        continue;
    // Provide the Host Information.
$tHost = 'gateway.push.apple.com'; //gateway.push.apple.com
$tPort = 2195;

// Provide the Certificate and Key Data.
$tCert = 'ck_dis.pem';

// Provide the Private Key Passphrase (alternatively you can keep this secrete
// and enter the key manually on the terminal -> remove relevant line from code).
// Replace XXXXX with your Passphrase
$tPassphrase = 'aiyidian2014';

// The message that is to appear on the dialog.
$tAlert = $msg;

// The Badge Number for the Application Icon (integer >=0).
$tBadge = 1;

// Audible Notification Option.
$tSound = 'default';

// The content that is returned by the LiveCode "pushNotificationReceived" message.
$tPayload = $msg;

// Create the message content that is to be sent to the device.
$tBody['aps'] = array (
    'alert' => $tAlert,
    'badge' => $tBadge,
    'sound' => $tSound,
    );
$tBody ['payload'] = $tPayload;

// Encode the body to JSON.
$tBody = json_encode ($tBody);

// Create the Socket Stream.
$tContext = stream_context_create ();
stream_context_set_option ($tContext, 'ssl', 'local_cert', $tCert);

// Remove this line if you would like to enter the Private Key Passphrase manually.
stream_context_set_option ($tContext, 'ssl', 'passphrase', $tPassphrase);

// Open the Connection to the APNS Server.
$tSocket = stream_socket_client ('ssl://'.$tHost.':'.$tPort, $error, $errstr, 30, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $tContext);

// Check if we were able to open a socket.
if (!$tSocket)
    exit ("APNS Connection Failed: $error $errstr" . PHP_EOL);

// Build the Binary Notification.
$tMsg = chr (0) . chr (0) . chr (32) . pack ('H*', $tToken) . pack ('n', strlen ($tBody)) . $tBody;

// Send the Notification to the Server.
$tResult = fwrite ($tSocket, $tMsg, strlen ($tMsg));

if (!$tResult) {
    $sok = false;
    echo 'ERROR';
	break;
}

// Close the Connection to the Server.
fclose ($tSocket);
}
if ($sok)
	echo 'OK';
mysql_free_result($result);
mysql_close($con);
?>
