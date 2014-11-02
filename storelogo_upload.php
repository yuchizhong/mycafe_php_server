<?php
$c = file_get_contents("php://input"); //$GLOBALS['HTTP_RAW_POST_DATA'];

if (!is_dir('images/store' . $_GET["storeid"] . '/logoimage'))
    mkdir('images/store' . $_GET["storeid"] . '/logoimage', 0777, true);

$n = 'images/store' . $_GET["storeid"] . '/logoimage/' . $_GET["filename"];
$fp = fopen($n,'w+');  
fwrite($fp, $c, strlen($c));
fclose($fp);
echo "OK";
?>
