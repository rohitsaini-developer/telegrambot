<?php
declare (strict_types = 1);

namespace app\controller;

use think\facade\Db;
use think\facade\Session;
use think\Request;
use think\facade\View;


class Login 
{
    
    public function login(){
        if(session('admin')){
            return redirect('setting_bot');
        }
       return View::fetch('login/login');
    }
    
    
    //请求
    
    public function dulogin(Request $request){

        if(session('admin')){
            return redirect('setting_bot');
        }
        
        $name       =   $request->post('name');
        $password   =   $request->post('password');
        $vfe        =   $request->post('vfe');
        
        if(!$name){
            exit(json_encode(array('code'=>1,'msg'=>'用户名为空')));
        }
        if(!$password){
            exit(json_encode(array('code'=>1,'msg'=>'密码为空')));
        }
        //判断验证码是否正确

        if(!captcha_check($vfe)){
            exit(json_encode(array('code'=>1,'msg'=>'验证码错误')));
        }
        
        $admin=Db::table('admin')->where(array('name'=>$name))->find();
      
        if(!$admin){
            exit(json_encode(array('code'=>1,'msg'=>'用户名或密码错误')));
        }
        
        if($admin['password'] != md5($password)){
            exit(json_encode(array('code'=>1,'msg'=>'用户名或密码错误')));
        }
        
        session('admin',$admin);
        
        return json(array('code'=>0,'msg'=>'登录成功'));
       
    }
    
    //退出登录
    public function outlogin(){
        Session::destroy();
        return redirect('login')->with('success','退出成功');
    }

}
