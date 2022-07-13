<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use app\model\Product as ProductModel;

class Product extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $products['title1']='Game List';
        $products = ProductModel::select();

        return view('admin/product/index', compact('products'));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return view('admin/product/create');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $product = ProductModel::create($request->all());

        return json(array('code'=>0,'msg'=>'Successfully saved'));
        
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $product = ProductModel::find($id);
        return view('admin/product/show', compact('product'));
        
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $product = ProductModel::find($id);
        return view('admin/product/edit', compact('product'));
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $product = ProductModel::find($id);

        $product->name =  $request['name'];
        $product->code =  $request['code'];
        $product->status =  $request['status'];

        $product->save();

        return redirect(env('app.app_url').'product');
        
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $product = ProductModel::find($id)->delete();
        return json(array('code'=>0,'msg'=>'Successfully deleted'));
    }
}
