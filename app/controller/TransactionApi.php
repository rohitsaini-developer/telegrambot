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
        $this->aptechApiUrl = 'https://aptech88.com/api/';
        $this->apiController = new Api;

    }

    /**
     * pindeposit :- deposit amount 
     * Parameters :- $token,$url,$update_id,$chat_id,$message_id,$text,lastCommand
     *
     */
    public function pindeposit($token,$url,$update_id,$chat_id,$message_id,$text,$lastCommand){

        $bot_id = Db::table('master_bot')->where('token',$token)->value('id');
       
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

        }else if(($text == 'DIGI' || $text == 'HOTLINK' || $text == 'CELCOM') && in_array($lastCommand,array('/pindeposit','/restart'))){

            $messageData = urlencode("Please enter your pin code 👇\nExample:- PIN-25456581545458");

            file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                
            $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);


        }else if (explode('-',$text)[0] == 'PIN' && in_array($lastCommand,array('DIGI','HOTLINK','CELCOM')) ){

            $pin = trim(explode('-',$text)[1]);
            if(preg_match('/^\d{14}$/', $pin) || preg_match('/^\d{16}$/', $pin)){

                $user_id = Db::table('tg_tp88user')->where('bot_id',$bot_id)->where('chat_id',$chat_id)->value('tuid');

                $pinAtttempTimeCheck = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('status','Failed')->where('pin_attempt',3)->find();

                $current_time = date('Y-m-d H:i:s'); 
                $pin_time = $pinAtttempTimeCheck['pin_time'] ?? ''; 
                
                $current_time = date_create($current_time); 
                $diff = $current_time->diff(date_create($pin_time)); 

                if(!is_null($pinAtttempTimeCheck) && $diff->i <= 15){
                    
                    $messageData = urlencode("🚫 Maximum attempt has been completed. Please try again after 15 minutes. \n/restart");
    
                    file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                        
                    $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);

                }else{
                    $message = '';

                    $checkPin = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('pin_number',$pin)->where('status','Success')->find();
                    if(!is_null($checkPin)){

                        $message = "This pin is already used. Please try again.\n/restart";

                    }else{
        
                        $salt =  "aptech88";
                        $authkey = md5($salt."tsy520");
                        $user  = Db::table('master_bot')->where('id',$bot_id)->value('aptech_id');
                        $telco = $lastCommand;
                        $ussd  = $pin;
                        
                        //Deposit api
                        $depositApiUrl = $this->aptechApiUrl.'tgdeposit/'.$authkey.'/'.$user.'/'.$telco.'/'.$ussd;
                        $response_deposit = getApiData($depositApiUrl);

                        $checkStatus = $response_deposit['msg'] ?? 'Failed';

                        if($checkStatus == 'Success'){

                            //Pin Process api
                            $pinProcessApi =  $this->aptechApiUrl.'tgpinprocess/'.$authkey.'/'.$response_deposit['data'];
                            $response = getApiData($pinProcessApi);

                            //Start pin 
                            $entryExists = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->whereIn('status','Failed')->find();
                            if(!is_null($entryExists)){

                                $updateData['pin_number']  = $pin;
                                $updateData['code']        = $response['data']['code'] ?? null;
                                $updateData['amount']      = $response['data']['amount'] ?? null;
                                $updateData['pin_time']    = date('Y-m-d H:i:s');
                                // $updateData['pin_attempt'] = 0;
                                $updateData['status']      = $response['msg'] ?? 'Failed';
                                $updateData['response']    = json_encode($response);
            
                                Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->whereIn('status','Failed')->update($updateData);
            
                            }else{
                                $insertData['bot_id']      = $bot_id;
                                $insertData['user_id']     = $user_id;
                                $insertData['pin_number']  = $pin;
                                $insertData['code']        = $response['data']['code'] ?? null;
                                $insertData['amount']      = $response['data']['amount'] ?? null;
                                $insertData['pin_time']    = date('Y-m-d H:i:s');
                                // $insertData['pin_attempt'] = 0;
                                $insertData['status']      = $response['msg'] ?? 'Failed';
                                $insertData['response']    = json_encode($response);
            
                                Db::table('pin_history')->insert($insertData);
                            }

                            $checkPinStatus = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('pin_number',$pin)->find();

                            if(!is_null($checkPinStatus) && $checkPinStatus['status'] == 'Success'){

                                // Update pin attempt
                                Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('pin_number',$pin)->update(array('pin_attempt'=>0));

                                //Wallet
                                $walletData['bot_id']  = $bot_id;
                                $walletData['user_id'] = $user_id;
                                $walletData['pin_id']  = $checkPinStatus['id'];
                                $walletData['amount']  = $response['data']['amount'] ?? null;
            
                                Db::table('wallet')->insert($walletData);

                                $message = "Thank you for deposit amount.";

                                // Send Menu
                                $api=Db::table('api')->alias('a')->join('api_gid b','a.gid=b.gid','LEFT')
                                ->where(array('keywords'=>'/menu'))->field('a.gid as agid,a.*,b.*')->find();
            
                                file_get_contents($url . $api['api'].urlencode("{$api['text']}")."&".$api['param']."&chat_id=".$chat_id);
                    
                                $messageContent = "API ID: ".$api['id']." has been sent to user.";
                    
                                $this->savechatdata(0,$messageContent,'SYSTEM',$chat_id,$message_id);

            
                            }else{
                                $message = "🚫 Invalid Pin. Please try again.\n/restart";

                                $invalidPin = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('pin_number',$pin)->where('status','Failed')->find();

                                if(!is_null($invalidPin)){
                                    $updateData['pin_time']    = date('Y-m-d H:i:s');
                                    $updateData['pin_attempt'] = ($invalidPin['pin_attempt'] != 3) ? $invalidPin['pin_attempt']+1 : 1;
                
                                    Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('status','Failed')->update($updateData);
                                }

                            }
                            
                        }else{
                            $message = "🚫 Invalid Pin. Please try again.\n/restart";

                            $invalidPin = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('pin_number',$pin)->where('status','Failed')->find();

                            if(!is_null($invalidPin)){
                                $updateData['pin_time']    = date('Y-m-d H:i:s');
                                $updateData['pin_attempt'] = ($invalidPin['pin_attempt'] != 3) ? $invalidPin['pin_attempt']+1 : 1;
            
                                Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('status','Failed')->update($updateData);
                            }
                        }
                        
                    } 

                    $messageData = urlencode($message);

                    file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                        
                    $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);

                }
            }else{

                $user_id = Db::table('tg_tp88user')->where('bot_id',$bot_id)->where('chat_id',$chat_id)->value('tuid');
                
                $pinAtttempTimeCheck = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('status','Failed')->where('pin_attempt',3)->find();

                $current_time = date('Y-m-d H:i:s'); 
                $pin_time = $pinAtttempTimeCheck['pin_time'] ?? ''; 
                
                $current_time = date_create($current_time); 
                $diff = $current_time->diff(date_create($pin_time)); 

                if(!is_null($pinAtttempTimeCheck) && $diff->i <= 15){
                    
                    $messageData = urlencode("🚫 Maximum attempt has been completed. Please try again after 15 minutes. \n/restart");
    
                    file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                        
                    $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);

                }else{

                    $checkPin = Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('status','Failed')->find();
                    if(!is_null($checkPin)){
    
                        $updateData['pin_number']  = $pin;
                        $updateData['code']        = null;
                        $updateData['amount']      = null;
                        $updateData['pin_time']    = date('Y-m-d H:i:s');
                        $updateData['pin_attempt'] = ($checkPin['pin_attempt'] != 3) ? $checkPin['pin_attempt']+1 : 1;
                        $updateData['response']    = null;
    
                        Db::table('pin_history')->where('bot_id',$bot_id)->where('user_id',$user_id)->where('status','Failed')->update($updateData);
                    }else{
                        $inserData['bot_id']      = $bot_id;
                        $inserData['user_id']     = $user_id;
                        $inserData['pin_number']  = $pin;
                        $inserData['code']        = null;
                        $inserData['amount']      = null;
                        $inserData['pin_time']    = date('Y-m-d H:i:s');
                        $inserData['pin_attempt'] = 1;
                        $inserData['status']      = 'Failed';
                        $inserData['response']    = null;
    
                        Db::table('pin_history')->insert($inserData);
                    }
    
                    $messageData = urlencode("🚫 Invalid Pin Code. Please try again \n/restart");
    
                    file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                        
                    $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);

                }
                                
            }

        }else{
            $messageData = urlencode("🚫 Invalid deposit process. Please try again \n/restart");
    
            file_get_contents($url . "/sendmessage?text=".$messageData."&parse_mode=html&chat_id=".$chat_id);
                
            $this->apiController->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);
        }
        
    }


}