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
	
	$url='http://smsapi.c123.cn/OpenPlatform/OpenApi';           //�ӿڵ�ַ
	$ac='1001@501024590001';		                             //�û��˺�
	$authkey = 'DA139ED180D47E0F126AFC00E57C6362';		         //��֤��Կ
	$cgid='52';                                                  //ͨ������
	$c = '���İ��׵�ע����֤��Ϊ' . $randed . '�������Ʊ��ܲ��������롣';		 //����
	$m= $ID;	                                         //����
	$csid='';                                                   //ǩ����� ,����Ϊ��ʱ��ʹ��ϵͳĬ�ϵı��
	$t='';                                                       //����ʱ��,����Ϊ�ձ�ʾ��������,yyyyMMddHHmmss ��:20130721182038

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
		'action'=>'sendOnce',                                //�������� ��������sendOnce���ŷ��ͣ�sendBatchһ��һ���ͣ�sendParam	��̬�������Žӿ�
		'ac'=>$ac,					                         //�û��˺�
		'authkey'=>$authkey,	                             //��֤��Կ
		'cgid'=>$cgid,                                       //ͨ������
		'm'=>$m,		                                     //����,��������ö��Ÿ���
		'c'=>iconv('gbk','utf-8',$c),		                 //���ҳ����gbk���룬��ת��utf-8���룬�����ҳ����utf-8���룬����Ҫת��
		'csid'=>$csid,                                       //ǩ����� ������Ϊ�գ�Ϊ��ʱʹ��ϵͳĬ�ϵ�ǩ�����
		't'=>$t                                              //��ʱ���ͣ�Ϊ��ʱ��ʾ��������
		);
	$xml= postSMS($url,$data);			                     //POST��ʽ�ύ
    $re=simplexml_load_string(utf8_encode($xml));
	if(trim($re['result'])==1)                               //���ͳɹ� ��������ҵ��ţ�Ա����ţ����ͱ�ţ��������������ۣ����
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
	      echo "���ͳɹ�,����ֵΪ".$re['result'];
	     }
	 */
    }
    
	else  //����ʧ�ܵķ���ֵ
	{
	     switch(trim($re['result'])){
			case  0: echo "�ʻ���ʽ����ȷ(��ȷ�ĸ�ʽΪ:Ա�����@��ҵ���)";break; 
			case  -1: echo "�������ܾ�(�ٶȹ��졢��ʱ���IP���Ե�)�����ٶȹ������ʱ�ٷ�";break;
			case  -2: echo " ��Կ����ȷ";break;
			case  -3: echo "��Կ������";break;
			case  -4: echo "��������ȷ(���ݺͺ��벻��Ϊ�գ��ֻ����������࣬����ʱ������)";break;
			case  -5: echo "�޴��ʻ�";break;
			case  -6: echo "�ʻ����������ѹ���";break;
			case  -7:echo "�ʻ�δ�����ӿڷ���";break;
			case  -8: echo "����ʹ�ø�ͨ����";break;
			case  -9: echo "�ʻ�����";break;
			case  -10: echo "�ڲ�����";break;
			case  -11: echo "�۷�ʧ��";break;
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
		$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//תURL��׼��
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
