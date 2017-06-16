<?php
/**
 * 品牌
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:55
 */

namespace app\admin\controller;

use think\Request;

class Brand extends BaseController{

    protected $title = '品牌管理';

    /**
     * 列表
     */
    public function index(Request $request){
        $list = db('brand_stop')->select();
        $this->assign('list', $list);
        return $this->view();
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(){
        return $this->view();
    }

    /**
     *  新增
     *
     */
    public function save(Request $request){
        $params = $this->getReqParams(['name']);
        $rule = [
            'name' => 'require|max:64',
        ];
        validateData($params, $rule);

        $ret = model('Brand', 'logic')->addBrandStop($params);
        if($ret['code'] != 2000){
            $this->error($ret['msg']);
        }
        $this->success('添加成功','');

    }

    /**
     *详情
     */
    public function read($id){

    }


    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id){
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int            $id
     * @return \think\Response
     */
    public function update(Request $request, $id){
        $enable = $this->getReqParams(['value']);
        $ret = model('Brand', 'logic')->switchEnable($id,$enable['value']);
        if($ret['code'] != 2000){
            $this->error($ret['msg']);
        }
        $this->success('更新成功','');
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id){
        $ids = input('delete.id');
        $ret = db('brand_stop')->where("id IN ($ids)")->delete();
        if(!$ret){
            $this->error($ret['msg']);
        }
        $this->success('删除成功','');
    }


}