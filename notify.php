<?php
$input_data = json_decode(file_get_contents("php://input"), true);
if($input_data['object'] == 'charge')
{
    //TODO update database
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
