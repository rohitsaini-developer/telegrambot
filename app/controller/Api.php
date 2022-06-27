<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use think\Controller;
use think\facade\Db;
use think\facade\Log;

class Api
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
        
        $chat_id = $update['message']['chat']['id'];
        $name = $update['message']['from']['first_name'].' '.$update['message']['from']['last_name'];
        $text=$update['message']['text'];//获取用户消息
        
        $data['text']=$text;
        $data['name']=$name;
        $data['chat_id']=$chat_id;
        $data['time']=time();
        
         
        $tg_message=Db::table('tg_message')->insert($data);
        
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
        }
        
        //获取数据库关键词
        $api=Db::table('api')
        ->alias('a')
        ->join('api_gid b','a.gid=b.gid','LEFT')
        ->where(array('keywords'=>$data['text']))
        ->field('a.gid as agid,a.*,b.*')
        ->find();
        
        if($api){
          file_get_contents($url . "/sendmessage?text=".$api['text']."&chat_id=" . $chat_id);
          exit;
        }
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'forward me to groups']
                ]
            ]
        ];

        $keyboard = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => 'forward me to groups 🤖', 'callback_data' => 'someString']
                ]
            ]
        ]);
       

        if($data['text']=='/register'){
            $messageData = 'hello welocome to game bot&reply_markup='.$keyboard;
            sendMessage($chat_id,$messageData,$token);
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
        
        //获取数据库关键词
        $api = Db::table('api')
        ->alias('a')
        ->join('api_gid b','a.gid=b.gid','LEFT')
        ->where(array('keywords'=>$data['text']))
        ->field('a.gid as agid,a.*,b.*')
        ->find();
        dump($api);
    }

    public function callApi(){

        $allApis = [
            'opcredit' =>  [
                'op'   => 'a001',
                'sign' => 'a001L3eFthWAUAXDsg5c1eOZP3qpDZAgo8ga'
            ],
            'createplayer'=>[
                'op'   => 'tp88',
                'mem'  => 'easytogo',
                'pass' => 'Abc123',
                'sign' => '22745fa14fc267cb81ef318d0d90d52e'  
            ],
            'getappurl' =>[
                'op'   => 'tp88',
                'prod' => 16,
                'sign' => '6bdf1fc57d1e0ff4ee4fbf13534db55f'
            ],
            'getappusername' =>[
                'op'   => 'tp88',
                'mem'  => 'easytogo',
                'prod' => 16,
                'sign' => '30038261031535e3989936e155b5834b'
            ],
            'balance' =>[
                'op'   => 'tp88',
                'prod' => 16,
                'mem'  => 'easytogo',
                'pass' => 'Abc123',
                'sign' => '30038261031535e3989936e155b5834b'
            ],
            'deposit' =>[
                'op'     => 'tp88',
                'prod'   => 16,
                'ref_no' => 'DEP000001',
                'amount' => 10.00,
                'mem'    => 'easytogo',
                'pass'   => 'Abc123',
                'sign'   => '8a6ef5521f98171a38f92e07d01c21c1'
            ],
            'withdraw' =>[
                'op'     => 'tp88',
                'prod'   => 16,
                'ref_no' => 'WIT000001',
                'amount' => 10.00,
                'mem'    => 'easytogo',
                'pass'   => 'Abc123',
                'sign'   => 'a76ec014644b2defac4d896e7b6d3775'
            ],
            'game' =>[
                'type'   => 2,
                'h5'     => 1,
                'lang'   => "en-US",
                'op'     => 'tp88',
                'prod'   => 16,
                'ref_no' => 'WIT000001',
                'amount' => 10.00,
                'mem'    => 'easytogo',
                'pass'   => 'Abc123',
                'sign'   => '928751b9b801604c0d166195c8f53d04'
            ],
            'chgpass' =>[
                'op'     => 'tp88',
                'prod'   => 16,
                'mem'    => 'easytogo',
                'pass'   => 'Abc123',
                'sign'   => '2e3bdfcb30d1ab2506601e4f919100f2'
            ],
            'fetch' =>[
                'op'     => 'tp88',
                'key'    => 88888,
                'sign'   => 'b901e9f14110ed4f927d68143a91357f'
            ],
            'mark' =>[
                'op'     => 'tp88',
                'mark'   => [4409,4410,4411],
                'sign'   => '879047ea436581e5c40f567c9fea018a'
            ],
            'bethistory' =>[
                'op'     => 'tp88',
                'start'  => '2022-06-21 16:35:00',
                'end'    => '2022-06-21 16:40:00',
                'sign'   => 'afcf2de4b66c80d2ce24fb9014ba3fc7'
            ],
            'getgamelist' =>[
                'op'     => 'tp88',
                'prod'   => 16,
                'type'   => 1,
                'sign'   => '24f247a94486076855d5be63883fffe1'
            ],
            'callback'=>[
                'op'     => 'tp88',
                'mem'    => 'easytogo',
                'pass'   => 'Abc123',
                'prod'   => 16,
                'sign'   => 'dcae6a6021a05484e0688d2b6c904311'
            ]
        ];

        $response = [];
        foreach($allApis as $key=>$parameters){
            $apiUrl = 'https://api.easytogo123.com/';

            if( in_array($key , array('fetch','mark','bethistory')) ){

                $apiUrl = 'https://report.easytogo123.com/';

            }

            $response[$key] = api('POST',$apiUrl.$key,$parameters);
        }
        
        dd($response);

    }


}
