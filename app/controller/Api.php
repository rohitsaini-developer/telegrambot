<?php

declare (strict_types = 1);



namespace app\controller;



use think\Request;

use think\Controller;

use think\facade\Db;

use think\facade\Log;

use app\model\Product as ProductModel;



class Api extends GameApi

{

    /**

     * 显示资源列表

     *

     * @return \think\Response

     */

    public function index(){

        //设置连接根
        $admin=Db::table('admin')->where(array('id'=>1))->find();

        $token=$admin['token'];

        $url = "https://api.telegram.org/bot".$token;

        //获取反射信息

        $update = json_decode(file_get_contents('php://input'), true);

        Log::record($update);

        $chat_id = $update['message']['chat']['id'] ?? ''; // GET USER CHAT ID

        $first_name = $update['message']['from']['first_name'] ?? '';
        $last_name = $update['message']['from']['last_name'] ?? '';

        $name = $first_name.' '.$last_name; //GET USER NAME

        $text = $update['message']['text'] ?? ''; //GET CHAT DATA

        $message_id = $update['message']['message_id'] ?? ''; // GET MESSAGE ID

        $update_id = $update['update_id'] ?? ''; // GET UPDATE ID


        /**

         * 01/07/2022

         * KEYWORD API

         */

        //获取数据库关键词

        $api=Db::table('api')

        ->alias('a')

        ->join('api_gid b','a.gid=b.gid','LEFT')

        ->where(array('keywords'=>$text))

        ->field('a.gid as agid,a.*,b.*')

        ->find();

        $lastCommand = Db::table('tg_message')->where('update_id',$update_id-1)->where('name',$name)->value('text');
        
        // Set Session
        $checkUser = Db::table('tg_tp88user')->where('chat_id',$chat_id)->where('verify',0)->find();
        if(!is_null($checkUser)){
            $current = date('Y-m-d H:i:s');
            $sessionTime= strtotime($current.' + 3 minute');

            session('userId',$checkUser['tuid']);
            session('chatId',$checkUser['chat_id']);
            session('username',$checkUser['username']);
            session('phoneNumber',$checkUser['number']);
            session('isVerified',$checkUser['verify']);
            session('time',date("Y-m-d H:i:s",$sessionTime));
        }

        $isVerified = Db::table('tg_tp88user')->where('chat_id',$chat_id)->value('verify');
        session('isVerified',$isVerified);

        if($api){

            if(session('isVerified')){
                $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                file_get_contents($url . $api['api'].urlencode("{$api['text']}")."&".$api['param']."&chat_id=".$chat_id);

                $message = "API ID: ".$api['id']." has been sent to user.";

                $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
            }else{
                $messageContent = urlencode("⚠️Please verified your phone number first than use other command for execute.\n/verify");
                file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
                $message = "Please verified your phone number first.";
                $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
            }

            exit;

        }else{

            switch ($text) {
                case "/start":

                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                    $reply = urlencode("Welcome to TestBot");

                    file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);
        
                    $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);

                    // Send Menu
                    $api=Db::table('api')->alias('a')->join('api_gid b','a.gid=b.gid','LEFT')
                    ->where(array('keywords'=>'/menu'))->field('a.gid as agid,a.*,b.*')->find();

                    file_get_contents($url . $api['api'].urlencode("{$api['text']}")."&".$api['param']."&chat_id=".$chat_id);
        
                    $message = "API ID: ".$api['id']." has been sent to user.";
        
                    $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);                 
        
                break;
                case "/verify":
                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                    //Get phone number
                    $reply = urlencode('We will need your mobile contact to verify your account. Use "Verify" below for fast verification.');

                    // $requestPhoneKeyboard  = json_encode([
                    //     "keyboard" => [
                    //         [
                    //             [
                    //                 'text'=> 'Phone Number',
                    //                 'callback_data' => 'Phone Number',
                    //                 "one_time_keyboard" => true,
                    //                 "request_contact" => true,

                    //             ],
                    //             [
                    //                 'text'=>'Cancel',
                    //                 'callback_data'=>'Cancel',
                    //             ],
                    //         ]
                            
                    //     ],
                    //     "resize_keyboard" => true,
                    //     "one_time_keyboard" => true,
                    //     "selective" => true,
                    // ]);

                    // file_get_contents($url . "/sendmessage?text=".$reply."&reply_markup=".urlencode($requestPhoneKeyboard )."&chat_id=" . $chat_id);

                    file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);
                    
                    $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);   
                break;
                case is_numeric($text) && strlen($text)>6 && ($lastCommand == '/verify'):
                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                    $verificationCode = rand(6,999999);

                    $phoneVerifyAPI = 'https://aptech88.com/api/tgverify/520ba4358a6ae41b2cf5df2207a7b001/'.$text.'/'.$verificationCode;

                    $response = getApiData($phoneVerifyAPI);

                    Log::record($response);
                    
                    if($response['code'] == 0 && (isset($response['msg']) && $response['msg'] == 'Success')){
                        $bot = Db::table('master_bot')->where('token','=',$token)->find();
                        
                        $record = array(
                            'bot_id'       => $bot['id'],
                            'chat_id'      => $chat_id,
                            'username'     => strtolower($first_name),
                            'password'     => md5('Aabb@8899'),
                            'player_code'  => 'MY8'.rand(4,9999),
                            'name'         => $bot['name'],
                            'number'       => $text,
                            'verification_code' => $verificationCode
                        );
                        
                        
                        $userExists = Db::table('tg_tp88user')->where('bot_id', $bot['id'])->where('chat_id',$chat_id)->find();
                        if(is_null($userExists)){
                            $user = Db::table('tg_tp88user')->save($record);

                            // Log::record($user);

                        }else{
                            $user = Db::table('tg_tp88user')->where('tuid',$userExists['tuid'])->update(['number'=> $text,'verification_code'=>$verificationCode]);

                            // Log::record($user);

                        }

                        $reply = urlencode("Message sent successfully.\nPlease verify your code.\n/resend");

                        file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);
            
                        $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);
                    }

                    // Log::record(session('isVerified'));
                    
                break;
                case is_numeric($text) && strlen($text)<=6 && is_numeric($lastCommand):

                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                    $verifyCode = Db::table('tg_tp88user')->where('chat_id',$chat_id)->where('verification_code',$text)->find();

                    if(!is_null($verifyCode)){

                        //Create player on game api
                        $parameterPlayer = array(
                            "op"   => env('gameapi.op'),
                            "mem"  => $verifyCode['username'],
                            "pass" => "Aabb8899",
                        );

                        $parameterPlayer['sign'] = md5($parameterPlayer['mem'].$parameterPlayer['op'].$parameterPlayer['pass'].env('gameapi.game_api_secret_key'));

                        $responsePlayer = $this->executeGameApiCommand($url,'/createplayer',$parameterPlayer,$update_id,$name,$chat_id,$message_id);
                        // End Create player api

                        Db::table('tg_tp88user')->where('tuid',$verifyCode['tuid'])->update(['verification_code'=>null,'verify'=>1]);

                        // $reply = urlencode("Phone number verified successfully.");

                        // file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);
            
                        // $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);

                        $reply = urlencode("Welcome to TestBot");

                        file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);
            
                        $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);
    
                        // Send Menu
                        $api=Db::table('api')->alias('a')->join('api_gid b','a.gid=b.gid','LEFT')
                        ->where(array('keywords'=>'/menu'))->field('a.gid as agid,a.*,b.*')->find();
    
                        file_get_contents($url . $api['api'].urlencode("{$api['text']}")."&".$api['param']."&chat_id=".$chat_id);
            
                        $message = "API ID: ".$api['id']." has been sent to user.";
            
                        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);

                        //Send Balance details
                        $getUserDetail = Db::table('tg_tp88user')->where('chat_id',$chat_id)->find();

                        $parameter = array(
                            'op'   => env('gameapi.op'),
                            'prod' => env('gameapi.prod'),
                            'mem'  => $getUserDetail['username'],
                            'pass' => 'Aabb8899',
                        );

                        $parameter['sign'] = md5($parameter['mem'].$parameter['op'].$parameter['pass'].$parameter['prod'].env('gameapi.game_api_secret_key'));

                        $apiResponse = $this->executeGameApiCommand($url,'/balance',$parameter,$update_id,$name,$chat_id,$message_id);

                        if($apiResponse['desc'] == 'SUCCESS'){
                            $messageContent = urlencode("🔮{$getUserDetail['username']} {$getUserDetail['player_code']},\n\n💰Baki Wallet: RM{$apiResponse['balance']} \n");

                            file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
        
                            $message = "Balance response has been sent to user.";
        
                            $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                        }
            
                    }else{
                        $reply = urlencode("Invalid Verification Code.\n/resend");

                        file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);
            
                        $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);
                    }
                    
                break;
                case "/resend":
                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);
                    // session_unset();
                    $verificationCode = rand(6,999999);

                    $getUser = Db::table('tg_tp88user')->where('chat_id',$chat_id)->find();

                    $phoneVerifyAPI = 'https://aptech88.com/api/tgverify/520ba4358a6ae41b2cf5df2207a7b001/'.$getUser['number'].'/'.$verificationCode;

                    $response = getApiData($phoneVerifyAPI);

                    if($response['code'] == 0 && $response['msg'] == 'Success'){

                        if(!is_null($getUser)){
                            $user = Db::table('tg_tp88user')->where('tuid',$getUser['tuid'])->update(['verification_code'=>$verificationCode]);
                        }

                        $reply = urlencode("Message sent successfully.\nPlease verify your code.\n/resend");

                        file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);
            
                        $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);

                    }
        
                break;
                case "/createplayer":
                    if(session('isVerified')){
                        $bot = Db::table('master_bot')->where('token','=',$token)->find();

                        $record = array(

                            'bot_id'       => $bot['id'],
                            'chat_id'      => $chat_id,
                            'username'     => strtolower($first_name),
                            'password'     => md5('Aabb@8899'),
                            'player_code'  => 'MY8'.rand(4,9999),
                            'name'         => $bot['name'],

                        );

                        $userExists = Db::table('tg_tp88user')->where('bot_id', $bot['id'])->where('chat_id',$chat_id)->find();

                        if(is_null($userExists)){

                            $user = Db::table('tg_tp88user')->save($record);
                            
                            //Create player on game api
                            $parameter = array(
                                "op"   => env('gameapi.op'),
                                "mem"  => $record['username'],
                                "pass" => "Aabb8899",
                            );

                            $parameter['sign'] = md5($parameter['mem'].$parameter['op'].$parameter['pass'].env('gameapi.game_api_secret_key'));

                            // Log::record($parameter);

                            $response = $this->executeGameApiCommand($url,'/createplayer',$parameter,$update_id,$name,$chat_id,$message_id);

                            Log::record($response);
                            
                        }

                        $getUserDetail = Db::table('tg_tp88user')->where('bot_id', $bot['id'])->where('chat_id',$chat_id)->find();

                        $messageContent = urlencode("🔮{$getUserDetail['username']} {$getUserDetail['player_code']},\n\n💰Baki Wallet: RM0.00 \n\n🔮Baki RP : 0 (💰 RM 0)\n");

                        $this->savechatdata($update_id,$messageContent,$name,$chat_id,$message_id);

                        file_get_contents($url . "/sendmessage?text={$messageContent}"."&parse_mode=html&chat_id=".$chat_id);
            
                        $message = "Welcome message has been sent to user.";
            
                        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                    }else{
                        $messageContent = urlencode("⚠️Please verified your phone number first than use other command for execute.\n/verify");
                        file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
                        $message = "Please verified your phone number first.";
                        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                    }
                break;
                case "/opcredit":
                    if(session('isVerified')){
                        $parameter = array(
                            'op'  => env('gameapi.op'),
                            'sign'=> md5('tp88'.env('gameapi.game_api_secret_key'))
                        );

                        $apiResponse = $this->executeGameApiCommand($url,$text,$parameter,$update_id,$name,$chat_id,$message_id);

                        if($apiResponse['desc'] == 'SUCCESS'){
                            $messageContent = urlencode("💰Baki Kredit: RM{$apiResponse['credit']} \n");

                            file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
        
                            $message = "opcredit response has been sent to user.";
        
                            $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                        }
                    }else{
                        $messageContent = urlencode("⚠️Please verified your phone number first than use other command for execute.\n/verify");
                        file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
                        $message = "Please verified your phone number first.";
                        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                    }

                break;
                case "/balance":
                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);
                    if(session('isVerified')){
                        $getUserDetail = Db::table('tg_tp88user')->where('chat_id',$chat_id)->find();
                        $parameter = array(
                            'op'   => env('gameapi.op'),
                            'prod' => env('gameapi.prod'),
                            'mem'  => $getUserDetail['username'],
                            'pass' => 'Aabb8899',
                        );
    
                        $parameter['sign'] = md5($parameter['mem'].$parameter['op'].$parameter['pass'].$parameter['prod'].env('gameapi.game_api_secret_key'));
    
                        $apiResponse = $this->executeGameApiCommand($url,$text,$parameter,$update_id,$name,$chat_id,$message_id);
    
                        if($apiResponse['desc'] == 'SUCCESS'){
                            $messageContent = urlencode("🔮{$getUserDetail['username']} {$getUserDetail['player_code']},\n\n💰Baki Wallet: RM{$apiResponse['balance']} \n");

                            file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);

                            $message = "Balance response has been sent to user.";
                            $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                        }
                    }else{
                        $messageContent = urlencode("⚠️Please verified your phone number first than use other command for execute.\n/verify");
                        file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
                        $message = "Please verified your phone number first.";
                        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                    }
                   
                    
                break;
                case "/idbaru":
                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);
                    if(session('isVerified')){
                        $products = ProductModel::where('status','active')->column('name');
                        
                        $messageData = urlencode("Untuk permainan mana anda\ningin meminta ID baru? sila pilih\nyang berikut 👇");
                        
                        $inlineKeyboardArray = array();
                        foreach($products as $key=>$product){
                            $inlineKeyboardArray[$key]['text'] = $product;
                            $inlineKeyboardArray[$key]['callback_data'] = $product;
                        }
                        
                        $productkeyboard = json_encode([
                            "inline_keyboard" => array_chunk($inlineKeyboardArray,2),
                            "resize_keyboard" => true,
                        ]);
                        
                        file_get_contents($url . "/sendmessage?text=".$messageData."&reply_markup=".urlencode($productkeyboard)."&chat_id=" . $chat_id);
                        $this->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);
                    }else{
                        $messageContent = urlencode("⚠️Please verified your phone number first than use other command for execute.\n/verify");
                        file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
                        $message = "Please verified your phone number first.";
                        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                    }
                break;
                case in_array(explode('-',$text)[0],array("/pindeposit","/restart","DIGI","HOTLINK","CELCOM","PIN")):

                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                    if(session('isVerified')){
                        $transaction = new TransactionApi;

                        $response = $transaction->pindeposit($token,$url,$update_id,$chat_id,$message_id,$text,$lastCommand);
                    }else{
                        $messageContent = urlencode("⚠️Please verified your phone number first than use other command for execute.\n/verify");
                        file_get_contents($url . "/sendmessage?text={$messageContent}&parse_mode=html&chat_id=".$chat_id);
                        $message = "Please verified your phone number first.";
                        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
                    }
                break;
                default:
                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                    file_get_contents($url . "/sendmessage?text=".urlencode("Please send what command is available. ".$text." Command not found.")."&chat_id=" . $chat_id);
        
                    $message = "COMMAND NOT FOUND AND REPLY BY BOT";
        
                    $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);

            }

            exit;

        }

    }



    public function ceshi(){

        
        // $data['text'] = input('get.text');

        $admin=Db::table('admin')->where(array('id'=>1))->find();

        $token=$admin['token'];

        

        $url = "https://api.telegram.org/bot".$token;

        $products = ProductModel::column('name');
                    
        $messageData = urlencode("Untuk permainan mana anda\ningin meminta ID baru? sila pilih\nyang berikut 👇");

        $inlineKeyboardArray = array();
    
        foreach($products as $key=>$product){
            $inlineKeyboardArray[$key]['text'] = $product;
            $inlineKeyboardArray[$key]['callback_data'] = $product;
        }
        
        dd(array_chunk($inlineKeyboardArray,2));

        //获取数据库关键词

    }



    /**

     * 29/06/2022

     * SAVE CHAT DATA

     * $update_id

     * $text

     * $name

     * $chat_id

     * $message_id

     * $type = 1: SEND 2: REPLY 3: OTHER...

     */

    public function savechatdata($update_id,$text,$name,$chat_id,$message_id){

        $data['text']=$text;

        $data['name']=$name;

        $data['chat_id']=$chat_id;

        $data['message_id']=$message_id;

        $data['update_id']=$update_id;

        // $data['type']=$type;

        $data['time']=time();

        Db::table('tg_message')->insert($data);

    }


    /**
     * This function use for execute game api command and send response
     */
    public function executeGameApiCommand($url,$command,$parameter,$update_id,$name,$chat_id,$message_id){
      
        $command_text = str_replace("/","",$command);
        $getResponse = $this->{$command_text}($parameter);
       
        $this->savechatdata($update_id,$command,$name,$chat_id,$message_id);

        // Log::record($getResponse);

        // $response = http_build_query($getResponse,'',', ');

        // file_get_contents($url . "/sendmessage?text=".urlencode("{$response}")."&parse_mode=html&chat_id=".$chat_id);

        // $message = $command." response has been sent to user.";

        // $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);


        return $getResponse;
    }

   

}



