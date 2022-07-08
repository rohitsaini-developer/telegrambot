<?php

use think\facade\Log;


/**
 * API接口调用助手函数
 * @return array
 */
if (!function_exists('api')) {

    function api($method,$url='',$data=[])
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'api-key:'.env('gameapi.game_api_secret_key')
            ),
        ));

        $apiData = curl_exec($curl);

        curl_close($curl);

        return $responseData = json_decode($apiData,true);
    }

}  

/**
 * API接口调用助手函数
 * @return array
 */
if (!function_exists('getApiData')) {

    function getApiData($url='')
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'charset="utf-8"',
                'Accept:application/json'
            ),
        ));

        $apiData = curl_exec($curl);

        curl_close($curl);

        return $responseData = json_decode($apiData,true);
    }

}  

/**
 * sendMessage to telegram bot
 */
if(!function_exists('sendMessage')){
    function sendMessage($chatID, $data, $token) {
        
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatID."&parse_mode=html";
        $url = $url . "&text=" . $data;
        $ch = curl_init();
        $optArray = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
}

