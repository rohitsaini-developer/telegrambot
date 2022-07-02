<?php

declare (strict_types = 1);



namespace app\controller;



use think\Request;

use think\Controller;

use think\facade\Db;

use think\facade\Log;



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

        // Log::record($update);

        $chat_id = $update['message']['chat']['id'] ?? ''; // GET USER CHAT ID

        $first_name = $update['message']['from']['first_name'] ?? '';
        $last_name = $update['message']['from']['last_name'] ?? '';

        $name = $first_name.' '.$last_name; //GET USER NAME

        $text = $update['message']['text'] ?? ''; //GET CHAT DATA

        $message_id = $update['message']['message_id'] ?? ''; // GET MESSAGE ID

        $update_id = $update['update_id'] ?? ''; // GET UPDATE ID

        

        // file_get_contents($url . "sendmessage?text=Welcome to TestBot" ."&chat_id=" . $chat_id);

        // What this purpose ? Isset for ?

        // if(isset($update['message'])){



        //     $chat_id = $update['message']['chat']['id']; // GET USER CHAT ID

        //     $name = $update['message']['from']['first_name'].' '.$update['message']['from']['last_name']; //GET USER NAME

        //     $text = $update['message']['text']; //GET CHAT DATA

        //     $message_id = $update['message']['message_id']; // GET MESSAGE ID

            

        //     $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

           

        // }



        

        // $callback_text = $update['callback_query']['data'] ?? '';

        // $callback_message_id = $update['callback_query']['message']['message_id'] ?? '';

        // $replyToMessageId = $update['callback_query']['message']['reply_to_message']['message_id'] ?? '';

        // $callback_chat_id = $update['callback_query']['message']['chat']['id'] ?? '';

        // $phone = $update['callback_query']['message']['reply_to_message']['text'] ?? '';

        // $textType = $update['callback_query']['message']['reply_to_message']['entities'][0]['type'] ?? '';





        

      /*  

        if(is_numeric($data['text'])==true){

        //查Q绑

         $qq_bang = Db::connect([

            // 数据库类型

            'type'        => 'mysql',

            // 数据库连接DSN配置

            'dsn'         => '',

            // 服务器地址

            'hostname'    => '127.0.0.1',

            // 数据库名

            'database'    => 'qbang',

            // 数据库用户名

            'username'    => 'qbang',

            // 数据库密码

            'password'    => '25DtWExEBdbpHF5R',

            // 数据库连接端口

            'hostport'    => '',

            // 数据库连接参数

            'params'      => [],

            // 数据库编码默认采用utf8

            'charset'     => 'utf8',

            // 数据库表前缀

            'prefix'      => 'think_',

        ])

        ->table('8eqq')

        ->where(array('username'=>$data['text']))

        ->find();

        

            if($qq_bang){

                file_get_contents($url . "/sendmessage?text=绑定手机号：". $qq_bang['mobile'] ."&chat_id=" . $chat_id);

                exit;

            }else{

                file_get_contents($url . "/sendmessage?text=该QQ未泄露" ."&chat_id=" . $chat_id);

                exit;

            }

        }*/

        

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

        

        if($api){

            $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

            file_get_contents($url . $api['api'].urlencode("{$api['text']}")."&".$api['param']."&chat_id=".$chat_id);

            $message = "API ID: ".$api['id']." has been sent to user.";

            $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);

            exit;

        }else{

            switch ($text) {
                case "/start":
                    $bot = Db::table('master_bot')->where('token','=',$token)->find();

                    $record = array(

                        'bot_id'       => $bot['id'],
                        'chat_id'      => $chat_id,
                        'username'     => $name,
                        'player_code'  => 'MY8'.rand(4,9999),
                        'name'         => $bot['name'],

                    );

                    $userExists = Db::table('tg_tp88user')->where('bot_id', $bot['id'])->where('chat_id',$chat_id)->find();

                    if(is_null($userExists)){

                        $user = Db::table('tg_tp88user')->save($record);
                    }

                    $getUserDetail = Db::table('tg_tp88user')->where('bot_id', $bot['id'])->where('chat_id',$chat_id)->find();

                    $messageContent = urlencode("🔮{$getUserDetail['username']} {$getUserDetail['player_code']},\n\n💰Baki Wallet: RM0.00 \n\n🔮Baki RP : 0 (💰 RM 0)\n");

                    $this->savechatdata($update_id,$messageContent,$name,$chat_id,$message_id);

                    file_get_contents($url . "/sendmessage?text={$messageContent}"."&parse_mode=html&chat_id=".$chat_id);
        
                    $message = "Welcome message has been sent to user.";
        
                    $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
            
                break;
                // case "/opcredit":

                //     $parameter = array(
                //         'op'=>'a001',
                //         'sign'=>'a001L3eFthWAUAXDsg5c1eOZP3qpDZAgo8ga'
                //     );

                //     $this->executeGameApiCommand($url,$text,$parameter,$update_id,$name,$chat_id,$message_id);

                //     break;
                default:
                    $this->savechatdata($update_id,$text,$name,$chat_id,$message_id);

                    file_get_contents($url . "/sendmessage?text=Please send what command is available.".$text." Command not found."."&chat_id=" . $chat_id);
        
                    $message = "COMMAND NOT FOUND AND REPLY BY BOT";
        
                    $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);

            }

            exit;

        }

        

        //获取数据库关键词

        // $api = Db::table('api')

        // ->alias('a')

        // ->join('api_gid b','a.gid=b.gid','LEFT')

        // ->where(array('keywords'=>$data['text']))

        // ->field('a.gid as agid,a.*,b.*')

        // ->find();

        

        // if($api){

        //     file_get_contents($url . "/sendmessage?text=". $api['text'] ."&chat_id=" . $chat_id);

        //     exit;

        // }

        // dd($api,$api['api']);

        

       

        if($text=='/register'){

            $reply = urlencode("Please keyin your mobile number for verify.");

            file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $chat_id);

            $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);

            exit;

        }



        if(is_numeric($text)){

            

            $phone = $text;

            $bot = Db::table('master_bot')->where('token','=',$token)->find();

           
            $updatPhoneNumber = array(

                'bot_id'  => $bot['id'],

                'chat_id' => $chat_id,

                'name'    => $bot['name'],

                'number'  => $phone,

            );



            $userExists = Db::table('tg_tp88user')->where('bot_id', $bot['id'])->where('chat_id',$chat_id)->find();



            if(is_null($userExists)){

                $user = Db::table('tg_tp88user')->save($updatPhoneNumber);

            }else{

                $user = Db::table('tg_tp88user')->where('chat_id', $chat_id)->update($updatPhoneNumber);

            }

            

            $messageData = urlencode($phone." confirm your phone number?");



            // Create keyboard

            $keyboard = json_encode([

                "inline_keyboard" => [

                    [

                        [

                            "text" => "Yes",

                            "callback_data" => "yes"

                        ],

                        [

                            "text" => "No",

                            "callback_data" => "no"

                        ],

                    ]

                ]

            ]);



            // file_get_contents($url . "/sendmessage?text=".$messageData."&reply_to_message_id=".$message_id."&reply_markup={$keyboard}&parse_mode=html&chat_id=" . $chat_id);

            file_get_contents($url . "/sendmessage?text=".$messageData."&reply_markup={$keyboard}&parse_mode=html&chat_id=" . $chat_id);

            $this->savechatdata($update_id,$messageData,'SYSTEM',$chat_id,$message_id);





            exit;

        }



        if($callback_text === 'yes'){





            // Send Verification code

            $parameter = json_encode([

                'phone_number' => "{$phone}",

                'api_id' => 13628466,

                'api_hash' => '84dead29a279eac6e474c26826ff8e48',

                'settings' => json_encode(array('allowFlashcall'=>true, 'currentNumber'=>true, 'allowAppHash'=>true)),

            ]);

            

            

            $curl = curl_init();



            curl_setopt_array($curl, array(

                CURLOPT_URL => $url."/auth.sendCode",

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_ENCODING => '',

                CURLOPT_MAXREDIRS => 10,

                CURLOPT_TIMEOUT => 0,

                CURLOPT_FOLLOWLOCATION => true,

                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

                CURLOPT_CUSTOMREQUEST => 'POST',

                CURLOPT_POSTFIELDS => $parameter,

                CURLOPT_HTTPHEADER => array(

                    'Content-Type: application/json',

                ),

            ));

    

            $curlData = curl_exec($curl);

    

            curl_close($curl);

    

            $responseData = json_decode($curlData,true);

            

            Log::record($responseData);

            



            $phone_number = Db::table('tg_tp88user')->where('chat_id', $callback_chat_id)->value('number');

            // remove keyboard

            $data = http_build_query([

                'text' => $phone_number.' confirm your phone number?',

                'chat_id' => $callback_chat_id,

                'message_id' => $callback_message_id

            ]);

            

            file_get_contents($url."/editMessageText?{$data}");



            //Send Reply

            $reply = urlencode("SMS contains 6-digit code has been sent to {$phone_number} \n\n if {$phone_number} is not your number press \n\n /reverifyphone \n\n to restart the verify process \n\n Please insert 6-digit verification code here:");



            file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $callback_chat_id);

            $this->savechatdata($update_id,$reply,'SYSTEM',$callback_chat_id,$callback_message_id);



            exit;

        }



        if($callback_text === 'no'){



            $phone_number = Db::table('tg_tp88user')->where('chat_id', $callback_chat_id)->value('number');



            // remove keyboard

            $data = http_build_query([

                'text' => $phone_number." confirm your phone number?",

                'chat_id' => $callback_chat_id,

                'message_id' => $callback_message_id

            ]);

            

            file_get_contents($url."/editMessageText?{$data}");



            $reply = urlencode("Your process is terminated because you choose no.");

            file_get_contents($url . "/sendmessage?text=".$reply."&chat_id=" . $callback_chat_id);

            $this->savechatdata($update_id,$reply,'SYSTEM',$callback_chat_id,$callback_message_id);



            exit;

        }

        

        if($callback_text=='/reverifyphone'){

            $reply = "Please keyin your mobile number for verify.";

            file_get_contents($url . "/sendmessage?text=".$reply."&reply_to_message_id=".$message_id."&chat_id=" . $chat_id);

            $this->savechatdata($update_id,$reply,'SYSTEM',$chat_id,$message_id);

            exit;

        }


        // if($data['text']=='/look@Azhe_php_bot'){

        // file_get_contents($url . "sendmessage?text=您可以私聊或回复我发送以下文字：胸大、甜美、大长腿、清纯、骚情" ."&chat_id=" . $chat_id);

        // exit;

        // }

        // if($data['text']=='胸大'){

        // file_get_contents($url . "sendPhoto?photo=https://images.pexels.com/photos/6113297/pexels-photo-6113297.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=500" ."&chat_id=" . $chat_id);

        // file_get_contents($url . "sendmessage?text=链接正在搭建中！！" ."&chat_id=" . $chat_id);

        // exit;

        // }

        

        // if($data['text']=='甜美'){

        // file_get_contents($url . "sendPhoto?photo=https://images.pexels.com/photos/6113297/pexels-photo-6113297.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=500" ."&chat_id=" . $chat_id);

        // file_get_contents($url . "sendmessage?text=链接正在搭建中！！" ."&chat_id=" . $chat_id);

        // exit;

        // }

        

        // if($data['text']=='大长腿'){

        // file_get_contents($url . "sendPhoto?photo=https://images.pexels.com/photos/6113297/pexels-photo-6113297.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=500" ."&chat_id=" . $chat_id);

        // file_get_contents($url . "sendmessage?text=链接正在搭建中！！" ."&chat_id=" . $chat_id);

        // exit;

        // }

        

        

        // if($data['text']=='清纯'){

        // file_get_contents($url . "sendPhoto?photo=https://images.pexels.com/photos/6113297/pexels-photo-6113297.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=500" ."&chat_id=" . $chat_id);

        // file_get_contents($url . "sendmessage?text=链接正在搭建中！！" ."&chat_id=" . $chat_id);

        // exit;

        // }

        

        // if($data['text']=='骚情'){

        // file_get_contents($url . "sendPhoto?photo=https://images.pexels.com/photos/6113297/pexels-photo-6113297.jpeg?auto=compress&cs=tinysrgb&dpr=2&w=500" ."&chat_id=" . $chat_id);

        // file_get_contents($url . "sendmessage?text=链接正在搭建中！！" ."&chat_id=" . $chat_id);

        // exit;

        // }

        

        // if($data['text']=='视频'){

        // file_get_contents($url . "sendAudio?audio=https://www.runoob.com/try/demo_source/movie.mp4" ."&chat_id=" . $chat_id);

        // exit;

        // }

        

        // if($data['text']=='标签'){

        // file_get_contents($url . "sendmessage?text=标签正在建设中！！" ."&chat_id=" . $chat_id);  

        // exit;

        // }

        

        // //发送给用户

        // file_get_contents($url . "sendmessage?text=你好，我是由红牛开发的一款演示机器人。具体操作：http://azhe.live" ."&chat_id=" . $chat_id);

    }



    public function ceshi(){

        
        $data['text'] = input('get.text');

        $admin=Db::table('admin')->where(array('id'=>1))->find();

        $token=$admin['token'];

        

        $url = "https://api.telegram.org/bot".$token;

        

        //获取数据库关键词

        $api = Db::table('api')

        ->alias('a')

        ->join('api_gid b','a.gid=b.gid','LEFT')

        ->where(array('keywords'=>$data['text']))

        ->field('a.gid as agid,a.*,b.*')

        ->find();

        // dd($api,$api['api']);

        //file_get_contents($url . $api['api'].urlencode($api['text'])."&".$api['param']."&chat_id=".$chat_id);

        $text = "\xE2\x9E\xA1 Sila tekan kata kunci perkhidmatan: \n\n /menu - Halaman Utama\xF0\x9F\x94\x8E \n\n \xF0\x9F\x8F\xA7 Deposit/Cuci/Pindah Kredit \n /pindeposit - Deposit dengan telco pin \n /cuci - Cuci masuk bank akaun anda \n /masukkredit - Dompet \xE2\x86\xAA Game ID \n /keluarkredit - Game ID \xE2\x86\xAA	Dompet";

        dd(file_get_contents($url . $api['api'] . urlencode($api['text']) . "&" . $api['param']."&chat_id=909509134"));

        // dd($text);

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

        $response = http_build_query($getResponse,'',', ');

        file_get_contents($url . "/sendmessage?text=".urlencode("{$response}")."&parse_mode=html&chat_id=".$chat_id);

        $message = $command." response has been sent to user.";

        $this->savechatdata(0,$message,'SYSTEM',$chat_id,$message_id);
    }

}



