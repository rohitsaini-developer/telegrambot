<?php
declare (strict_types = 1);

namespace app\controller;

use think\facade\Db;
use think\facade\View;

class Base 
{
    public function __construct(){
       
        $this->_admin = session('admin');
        // 未登录的用户不允许访问
		if(!$this->_admin){
			header('Location: login');
			exit;
		}
		
		$admin = Db::table('admin')->where(array('name'=>$this->_admin['name']))->find();

		View::assign('admin', $admin);
        
    }
}
