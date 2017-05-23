<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\DataService;
use think\Db;

class Material extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '物料管理';

    public function index(){
        //得到全部INFO

        $this->assign('title',$this->title);
        return view();
    }

    /**
     * 得到物料信息
     */

    public function getSupList(){
        $logicSupInfo = Model('Item','logic');
        $list = $logicSupInfo->getListInfo();
        return $list;
    }

    public function del(){

    }

    public function add(){

    }

    public function edit(){
        return view();
    }

    public function updataU9Info(){
        $logicItem = Model('Item','logic');
        $logicU9Item = Model('U9Item','logic');
        $u9List = $logicU9Item->getListInfo();
        $tempArr = [];
        if($u9List){
            foreach($u9List as $k => $v){
                //是否存在
                if($logicItem->exist($v)){
                    $tempArr[$k]['code'] = $v['code'];
                    $tempArr[$k]['name'] = $v['name'];
                    $tempArr[$k]['main_code'] = $v['main_code'];
                    $tempArr[$k]['main_name'] = $v['main_name'];
                    //$logicSupInfo->saveData($v);
                }
            }
        }
        if(!empty($tempArr)){
            $logicItem->saveAllData($tempArr);
        }
        return json(['code'=>200,'msg'=>'更新成功！']);
    }

    public function exportExcel(){

    }
}