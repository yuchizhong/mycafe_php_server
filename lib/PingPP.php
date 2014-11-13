<?php

// Tested on PHP 5.2, 5.3

// This snippet (and some of the curl code) due to the Facebook SDK.
if (!function_exists('curl_init')) {
  throw new Exception('PingPP needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('PingPP needs the JSON PHP extension.');
}
if (!function_exists('mb_detect_encoding')) {
  throw new Exception('PingPP needs the Multibyte String PHP extension.');
}

require('./lib/PingPP/PingPP.php');

// Channel constants
require('./lib/PingPP/Channel.php');

// Utilities
require('./lib/PingPP/Util.php');
require('./lib/PingPP/Util/Set.php');

// Errors
require('./lib/PingPP/Error.php');
require('./lib/PingPP/ApiError.php');
require('./lib/PingPP/ApiConnectionError.php');
require('./lib/PingPP/AuthenticationError.php');
require('./lib/PingPP/InvalidRequestError.php');
require('./lib/PingPP/RateLimitError.php');

// Plumbing
require('./lib/PingPP/Object.php');
require('./lib/PingPP/ApiRequestor.php');
require('./lib/PingPP/ApiResource.php');
require('./lib/PingPP/SingletonApiResource.php');
require('./lib/PingPP/AttachedObject.php');
require('./lib/PingPP/List.php');

// PingPP API Resources
require('./lib/PingPP/Charge.php');
require('./lib/PingPP/Refund.php');
