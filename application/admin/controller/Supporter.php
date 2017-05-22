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

class Supporter extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '供应商管理';

    public function index(){
        $this->assign('title',$this->title);

        return view();
    }

    public function showSupporter(){
        $logic = Model('Supporter','logic');
        $list = $logic->getListInfo();
        return $list;
    }

    public function updataU9info(){
        $logicSupInfo = Model('Supporter','logic');
        $logicU9SupInfo = Model('U9Supporter','logic');
        $u9List = $logicU9SupInfo->getListInfo();
        $tempArr = [];
        if($u9List){
            foreach($u9List as $k => $v){
                //是否存在
                if($logicSupInfo->exist($v)){
                    $tempArr[$k]['code'] = $v['code'];
                    $tempArr[$k]['name'] = $v['name'];
                    $tempArr[$k]['tax_code'] = $v['tax_code'];
                    $tempArr[$k]['mobile'] = $v['mobile'];
                    $tempArr[$k]['email'] = $v['email'];
                    $tempArr[$k]['ctc_name'] = $v['ctc_name'];
                    $tempArr[$k]['address'] = $v['address'];
                    $tempArr[$k]['pay_way'] = $v['pay_way'];
                    $tempArr[$k]['com_name'] = $v['com_name'];
                    $tempArr[$k]['type_code'] = $v['type_code'];
                    $tempArr[$k]['type_name'] = $v['type_name'];
                    //$logicSupInfo->saveData($v);
                }
            }
        }
        if(!empty($tempArr)){
            $logicSupInfo->saveAllData($tempArr);
        }
        return json(['code'=>200,'msg'=>'更新成功！']);
    }

    public function read(){
        echo 'a11';
    }
    public function del(){

    }

    public function add(){

    }

    public function edit(){
        return view();
    }



}