<?php
function send_push($msg, $tToken) {
$debug_env = false;

$tHost = 'gateway.push.apple.com'; //gateway.push.apple.com
if ($debug_env)
    $tHost = 'gateway.sandbox.push.apple.com';
$tPort = 2195;

// Provide the Certificate and Key Data.
$tCert = 'ck_dis.pem';
if ($debug_env)
    $tCert = 'ck_dev.pem';

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
$error = "";
$errstr = "";
$tSocket = stream_socket_client ('ssl://'.$tHost.':'.$tPort, $error, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $tContext);

// Check if we were able to open a socket.
if (!$tSocket)
    exit ("APNS Connection Failed: $error $errstr" . PHP_EOL);

// Build the Binary Notification.
$tMsg = chr (0) . chr (0) . chr (32) . pack ('H*', $tToken) . pack ('n', strlen ($tBody)) . $tBody;

// Send the Notification to the Server.
$tResult = fwrite ($tSocket, $tMsg, strlen ($tMsg));
if (!$tResult) {
    fclose ($tSocket);
    return false;
}
// Close the Connection to the Server.
fclose ($tSocket);
return true;
}
?>
