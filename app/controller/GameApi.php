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

    }

    /**
     * 
     * Check Operator Kiosk Credit
     * $parameters = array('op','sign')
     * 
     */
    public function opcredit($parameters = array()){

       return api('POST',$this->apiUrl.'opcredit',$parameters);
       
    }

    /**
     * 
     * Create Player
     * $parameters = array('op','mem','pass','sign')
     * 
     */
    public function createplayer($parameters = array()){

        return api('POST',$this->apiUrl.'createplayer',$parameters);
        
     }

    /**
     * 
     * Get Player Balance
     * $parameters = array('op','prod','mem','pass','sign')
     * 
     */
    public function balance($parameters = array()){

        return api('POST',$this->apiUrl.'balance',$parameters);
        
    }

    /**
     *
     * Deposit
     * $parameters = array('op','prod','ref_no','amount','mem','pass','sign')
     * 
     */
    public function deposit($parameters = array()){

        return api('POST',$this->apiUrl.'deposit',$parameters);
        
    }

    /**
     * 
     * withdraw
     * $parameters = array('op','prod','ref_no','amount','mem','pass','sign')
     * 
     */
    public function withdraw($parameters = array()){

        return api('POST',$this->apiUrl.'withdraw',$parameters);
        
    }
    
    /**
     *
     * Getappurl
     * $parameters = array('op','prod','sign')
     * Example Response : {{"err": 1,"desc": "SUCCESS","url": {"android": ["pi.d.918kiss.com","tm.d.918kiss.com "],"ios": ["pi.d.918kiss.com","tm.d.918kiss.com "]}}
     */
    public function getappurl($parameters = array()){

        return api('POST',$this->apiUrl.'getappurl',$parameters);
        
    }

}