<?php 

function seed() {
	list($msec, $sec) = explode(' ', microtime());
	return (float) $sec;
}

$operation = $_GET["OPERATION"];
$deviceID = $_GET["UUID"];

$con = mysql_connect("localhost", "root", "123456");
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
	
	srand(seed());
	$randed = rand(0123,9876);
	$newCode = "$randed";
	$result = mysql_query("INSERT INTO verification_code VALUES ($ID, $newCode)");
        mysql_free_result($result);
	
	$url='http://smsapi.c123.cn/OpenPlatform/OpenApi';           //接口地址
	$ac='1001@501024590001';		                             //用户账号
	$authkey = 'DA139ED180D47E0F126AFC00E57C6362';		         //认证密钥
	$cgid='52';                                                  //通道组编号
	$c = '您的爱易点注册验证码为' . $randed . '，请妥善保管并尽快输入。';		 //内容
	$m= $ID;	                                         //号码
	$csid='';                                                   //签名编号 ,可以为空时，使用系统默认的编号
	$t='';                                                       //发送时间,可以为空表示立即发送,yyyyMMddHHmmss 如:20130721182038

	sendSMS($url,$ac,$authkey,$cgid,$m,$c,$csid,$t);
	
	echo 'SENT_SMS';
} elseif ($operation == "REGISTER") {
	$ID = $_GET["ID"];
	$pass = $_GET["pass"];
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
	$result = mysql_query("INSERT INTO customers VALUES (NULL, $ID, '', '', '')");
	mysql_free_result($result);
	
	//get userID
	$result = mysql_query("SELECT MAX(customerID) FROM customers WHERE customerName='$ID'");
	while ($row = mysql_fetch_array($result)) { 
		$userID = $row["MAX(customerID)"];
		break;
	}
	mysql_free_result($result);
	
	$result = mysql_query("INSERT INTO user_login VALUES ($userID, $ID, $pass)");
	mysql_free_result($result);
	
	//login
	$result = mysql_query("DELETE FROM UUID_user WHERE UUID='$deviceID'");
	mysql_free_result($result);
	
	$result = mysql_query("INSERT INTO UUID_user VALUES ('$deviceID', '$userID', '$pushT')");
	mysql_free_result($result);
	
	mysql_query("COMMIT");
	
	echo 'OK';
} elseif ($operation == "GET_ID") {
	$username = "";
	$pushT = $_GET['push_token'];
	if ($pushT == NULL)
		$pushT = "";
	$result = mysql_query("SELECT username FROM UUID_user, user_login WHERE UUID='$deviceID' AND UUID_user.userID=user_login.userID");
	while ($row = mysql_fetch_array($result)) { 
		$username = $row["username"];
		if ($pushT != "") {
			mysql_query("UPDATE UUID_user SET push_token='$pushT' WHERE UUID='$deviceID'");
		}
		break;
	}
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
	$result = mysql_query("DELETE FROM UUID_user WHERE UUID='$deviceID'");
	mysql_free_result($result);
	
	$result = mysql_query("INSERT INTO UUID_user VALUES ('$deviceID', '$userID', '$pushT')");
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
	$result = mysql_query("DELETE FROM UUID_user WHERE UUID='$deviceID'");
	mysql_free_result($result);
	echo 'OK';
}

mysql_close($con);






function sendSMS($url,$ac,$authkey,$cgid,$m,$c,$csid,$t)
{
	$data = array
		(
		'action'=>'sendOnce',                                //发送类型 ，可以有sendOnce短信发送，sendBatch一对一发送，sendParam	动态参数短信接口
		'ac'=>$ac,					                         //用户账号
		'authkey'=>$authkey,	                             //认证密钥
		'cgid'=>$cgid,                                       //通道组编号
		'm'=>$m,		                                     //号码,多个号码用逗号隔开
		'c'=>iconv('gbk','utf-8',$c),		                 //如果页面是gbk编码，则转成utf-8编码，如果是页面是utf-8编码，则不需要转码
		'csid'=>$csid,                                       //签名编号 ，可以为空，为空时使用系统默认的签名编号
		't'=>$t                                              //定时发送，为空时表示立即发送
		);
	$xml= postSMS($url,$data);			                     //POST方式提交
    $re=simplexml_load_string(utf8_encode($xml));
	if(trim($re['result'])==1)                               //发送成功 ，返回企业编号，员工编号，发送编号，短信条数，单价，余额
	{
	     foreach ($re->Item as $item)
	  	 {
			 
			   $stat['msgid'] =trim((string)$item['msgid']);
		       $stat['total']=trim((string)$item['total']);
			   $stat['price']=trim((string)$item['price']);
			   $stat['remain']=trim((string)$item['remain']);
		       $stat_arr[]=$stat;
			
         }
	/*
		 if(is_array($stat_arr))
	     {
	      echo "发送成功,返回值为".$re['result'];
	     }
	 */
    }
    
	else  //发送失败的返回值
	{
	     switch(trim($re['result'])){
			case  0: echo "帐户格式不正确(正确的格式为:员工编号@企业编号)";break; 
			case  -1: echo "服务器拒绝(速度过快、限时或绑定IP不对等)如遇速度过快可延时再发";break;
			case  -2: echo " 密钥不正确";break;
			case  -3: echo "密钥已锁定";break;
			case  -4: echo "参数不正确(内容和号码不能为空，手机号码数过多，发送时间错误等)";break;
			case  -5: echo "无此帐户";break;
			case  -6: echo "帐户已锁定或已过期";break;
			case  -7:echo "帐户未开启接口发送";break;
			case  -8: echo "不可使用该通道组";break;
			case  -9: echo "帐户余额不足";break;
			case  -10: echo "内部错误";break;
			case  -11: echo "扣费失败";break;
			default:break;
		}
	}
	
}

function postSMS($url,$data='')
{
	$row = parse_url($url);
	$host = $row['host'];
	$port = $row['port'] ? $row['port']:80;
	$file = $row['path'];
	while (list($k,$v) = each($data)) 
	{
		$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
	}
	$post = substr( $post , 0 , -1 );
	$len = strlen($post);
	$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
	if (!$fp) {
		return "$errstr ($errno)\n";
	} else {
		$receive = '';
		$out = "POST $file HTTP/1.0\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Content-Length: $len\r\n\r\n";
		$out .= $post;		
		fwrite($fp, $out);
		while (!feof($fp)) {
			$receive .= fgets($fp, 128);
		}
		fclose($fp);
		$receive = explode("\r\n\r\n",$receive);
		unset($receive[0]);
		return implode("",$receive);
	}
}
?>
