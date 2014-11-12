<?php
require_once(dirname(__FILE__) . '/lib/PingPP.php');
$input_data = json_decode(file_get_contents("php://input"), true);
if (empty($input_data['channel']) || empty($input_data['amount']) || empty($input_data['storeID']) ||  empty($input_data['username'])) {
    echo 'ERROR_PARA';
    exit();
}
$amount = $input_data['amount'];
if (floatval($amount) == 0) {
    echo 'ERROR_ZERO';
    exit();
}
}
$channel = strtolower($input_data['channel']);
$orderNo = substr(md5(time()), 0, 12);

$key = 'sk_test_rrzLCSebzbT8SKiH4GX9SWH8';
$appid = 'app_5qjfH0GKyPy5y5ar';

//$extra 在渠道为 upmp_wap 和 alipay_wap 时，需要填入相应的参数，具体见技术指南。其他渠道时可以传空值也可以不传。
$extra = array();
switch ($channel) {
    case 'alipay_wap':
        $extra = array(
            'success_url' => 'http://www.yourdomain.com/success',
            'cancel_url' => 'http://www.yourdomain.com/cancel'
        );
        break;
    case 'upmp_wap':
        $extra = array(
            'result_url' => 'http://www.yourdomain.com/result?code='
        );
        break;
}

$storeName;

PingPP::setApiKey($key);
$ch = PingPP_Charge::create(
    array(
        "subject"   => "爱易点买单（" . $storeName . "）",
        "body"      => "共计￥" . $amount,
        "amount"    => $amount,
        "order_no"  => $orderNo,
        "currency"  => "cny",
        "extra"     => $extra,
        "channel"   => $channel,
        "client_ip" => $_SERVER["REMOTE_ADDR"],
        "app"       => array("id" => $appid)
    )
);

echo $ch;
