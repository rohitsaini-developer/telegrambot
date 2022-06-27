<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;


// Auth routes
Route::get('', 'admin/login/login');
Route::get('login', 'admin/login/login');
Route::post('dulogin', 'admin/Login/dulogin');
Route::post('logout', 'admin/Login/outlogin');

Route::get('captcha/[:id]', "\\think\\captcha\\CaptchaController@index");

// Pages routes
Route::get('index', 'index/Index/index');

Route::get('setting_bot', 'admin/Admin/setting_bot');

Route::get('admin', 'admin/Admin/admin');

Route::get('message', 'admin/Admin/message');

Route::get('huifu', 'admin/Admin/huifu');

Route::get('huifu_zn', 'admin/Admin/huifu_zn');

Route::get('huifu_bq', 'admin/Admin/huifu_bq');

Route::get('shangpin', 'admin/Admin/shangpin');

Route::get('shangpin_gid', 'admin/Admin/shangpin_gid');

Route::get('api', 'Api/index'); // telegram回调接口

Route::get('api1', 'Api/ceshi'); // api测试


Route::post('add_token', 'admin/Admin/add_token');

Route::post('add_api', 'admin/Admin/add_api');

Route::post('delete_api', 'admin/Admin/delete_api');

Route::post('add_shangpingid', 'admin/Admin/add_shangpingid');

Route::post('delete_shangpingid', 'admin/Admin/delete_shangpingid');

Route::post('add_shangpin', 'admin/Admin/add_shangpin');

Route::post('delete_shangpin', 'admin/Admin/delete_shangpin');

Route::post('update_img', 'admin/Admin/update_img');

Route::post('update_shangpinimg', 'admin/Admin/update_shangpinimg');

Route::post('index', 'admin/Index/index');

Route::get('call_api', 'Api/callApi');


