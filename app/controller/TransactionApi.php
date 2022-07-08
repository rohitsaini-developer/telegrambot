<?php
declare (strict_types = 1);

namespace app\controller;

use think\facade\Db;
use think\facade\Request;
use think\facade\Log;

class TransactionApi
{
    public function __construct(){

        /**
         * pindeposit :- deposit amount 
         * $authkey,$user,$telco,$ussd
         * $salt =  "aptech88";
         * $authkey = md5($salt."tsy520");
         * $user = Need setting a new column for bot. Everybot have different usercode
         * $telco = DIGI or CELCOM or HOTLINK
         * $ussd = 14-16 digit
         */
        //https://aptech88.com/api/tgdeposit/520ba4358a6ae41b2cf5df2207a7b001/user/telco/ussd
        $this->aptechApiUrl = 'https://aptech88.com/api/tgdeposit';
        $this->apiController = new Api;

    }

    /**
     * pindeposit :- deposit amount 
     * Parameters :- $url,$update_id,$chat_id,$message_id
     *
     */
    public function pindeposit($token,$url,$update_id,$chat_id,$message_id,$text,$lastCommand){

        if($text == '/pindeposit' || $text == '/restart'){
            $messageData = urlencode("Please select telco 👇");

            $keyboardButtons = json_encode([
                "keyboard" => [
                    [
                        [
                            'text'=> 'DIGI',
                            'callback_data' => 'DIGI',
                        ],
                        [
                            'text'=>'HOTLINK',
                            'callback_data'=>'HOTLINK',
                        ],
                        [
                            'text'=>'CELCOM',
                            'callback_data'=>'CELCOM',
                        ]
                    ]
                    
                ],
                "resize_keyboard" => true,
                "one_time_keyboard" => true,
                "selective" => true,
            ]);
    
            file_get_contents($url . "/sendmessage?text=".$messageData."&reply_markup=".urlencode($keyboardButtons)."&parse_mode=html&chat_id=" . $chat_id);


            $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);

        }else if($text == 'DIGI' || $text == 'HOTLINK' || $text == 'CELCOM'){

            $messageData = urlencode("Please enter your pin code 👇\nExample:- PIN-25456581545458");

            file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                
            $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);


        }else if (explode('-',$text)[0] == 'PIN'){

            $pin = trim(explode('-',$text)[1]);
            if(preg_match('/^\d{14}$/', $pin) || preg_match('/^\d{16}$/', $pin)){

                $message = '';
                $bot_id = Db::table('master_bot')->where('token',$token)->value('bot_id');

                $checkPin = Db::table('pin_history')->where('chat_id',$chat_id)->where('pin_number',$pin)->find();
                if(!is_null($checkPin)){

                    $message = 'Your pin is not valid. Please try again.\n/pindeposit';

                }else{

                    $inserData['bot_id']      = $bot_id;
                    $inserData['chat_id']     = $chat_id;
                    $inserData['pin_number']  = $pin;
                    $inserData['code']        = null;
                    $inserData['amount']      = null;
                    $inserData['pin_time']    = date('Y-m-d H:i:s');
                    $inserData['pin_attempt'] = 1;
                    $inserData['status']      = 'Process';
                    $inserData['response']    = json_encode($response);

                    $res = Db::table('pin_history')->insert($inserData);
                    
                }

                if($message == 'Success'){

                    $salt =  "aptech88";
                    $authkey = md5($salt."tsy520");
                    $user  = $chat_id;
                    $telco = $lastCommand;
                    $ussd  = $pin;
                    
                    $depositApiUrl = $this->aptechApiUrl.'/'.$authkey.'/'.$user.'/'.$telco.'/'.$ussd;
    
                    $response = getApiData($depositApiUrl);

                    $inserData['bot_id']      = $bot_id;
                    $inserData['chat_id']     = $chat_id;
                    $inserData['pin_number']  = $pin;
                    $inserData['code']        = $response['data']['code'];
                    $inserData['amount']      = $response['data']['amount'];
                    $inserData['pin_time']    = date('Y-m-d H:i:s');
                    $inserData['pin_attempt'] = 0;
                    $inserData['status']      = $response['msg'];
                    $inserData['response']    = json_encode($response);


                    $res = Db::table('pin_history')->insert($inserData);
    
                }

                $messageData = urlencode($message);

                file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                    
                $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);

            }else{

                $messageData = urlencode("🚫 Invalid Pin Code. Please try again \n/restart");

                file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                    
                $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);
            }
        }
        
    }



}