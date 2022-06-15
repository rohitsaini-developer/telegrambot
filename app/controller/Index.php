<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;

class Index extends BaseController
{
    public function index($gid){
        
        $data['shangpin'] = Db::table('shangpin')->where(array('gid'=>$gid))->select();
        
        $data['shangpin_gid'] = Db::table('shangpin_gid')->where(array('gid'=>$gid))->find();
        
        //获取Telegram用户名
        $data['admin'] = Db::table('admin')->where(array('id'=>1))->find();

        return view('index/index', compact('data'));

    }

}
