<?php
declare (strict_types = 1);

namespace app\controller;

use think\facade\Db;
use think\facade\Request;
use think\facade\Log;

class GameApi
{
    public function __construct(){

        $this->apiUrl       = 'https://api.easytogo123.com/';
        $this->reportApiUrl = 'https://report.easytogo123.com/';
        // $this->sign = 'a001L3eFthWAUAXDsg5c1eOZP3qpDZAgo8ga';

    }

    /**
     * 01/07/2022
     * Check Operator Kiosk Credit
     * $op
     * $sign
     */
    public function opcredit($parameters = array()){

       return api('POST',$this->apiUrl.'opcredit',$parameters);
       
    }

}