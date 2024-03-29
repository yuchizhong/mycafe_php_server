<?php

class PingPP_InvalidRequestError extends PingPP_Error
{
    public function __construct($message, $param, $httpStatus=null,
        $httpBody=null, $jsonBody=null
    )
    {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
        $this->param = $param;
    }
}
