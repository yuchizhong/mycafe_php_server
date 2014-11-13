<?php
$input_data = json_decode(file_get_contents("php://input"), true);
if($input_data['object'] == 'charge')
{
    //TODO update database
     $filename = "not.txt";
     $data = file_get_contents("php://input");
     file_put_contents($filename, $data, FILE_APPEND);
     $current_date = date("Ymd");
     $current_time = date("H:i"); //add s if need seconds
     file_put_contents($filename, "\n------------------'$current_date'-'$current_time'--------------------\n", FILE_APPEND);
     echo 'success';
}
else if($input_data['object'] == 'refund')
{
    //TODO update database
    echo 'success';
}
else
{
    echo 'fail';
}
