<?php
require_once(dirname(__FILE__) . '/lib/PingPP.php');

$key = 'sk_test_rrzLCSebzbT8SKiH4GX9SWH8';
$appid = 'app_5qjfH0GKyPy5y5ar';
$uname = "18501045153";
$amount = "100";

    PingPP::setApiKey($key);
    $ch = PingPP_Charge::create(
        array(
            "subject"   => "爱易点钱包充值",
            "body"      => "充值￥" . $amount . "（账号：" . $uname . "）",
            "amount"    => $amount,
            "order_no"  => "11111xxxxx",
            "currency"  => "cny",
            "extra"     => null,
            "channel"   => "alipay",
            "client_ip" => "222.222.222.222",
            "app"       => array("id" => $appid)
    	)
    );
    file_put_contents("not.txt", $ch);

 
