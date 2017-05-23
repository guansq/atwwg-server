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

    /**
     * 更新ERP供应商信息到数据库
     */
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

    /**
     * 得到供应商信息
     */

    public function getSupList(){
        $logicSupInfo = Model('Supporter','logic');
        $list = $logicSupInfo->getListInfo();
        $returnArr = [];
        foreach($list as $k => $v){
            $returnArr[] = [
                'code' => $v['code'],
                'name' => $v['name'],
                'type_name' => $v['type_name'],
                'tech_score' => $this->getTechScore(),
                'arv_rate' => '33333',
                'pp_rate' => '4444',
                'quali_score' => $this->getQualiScore(),
                'status' => '555555',
                'pay_type_status' => '555555',
                'quali' => '555555',
                'action' => '1111',
            ];

        }
        $info = ['data'=>$returnArr];
        $info = json_encode($info);
        echo
        '{
            "data": [
                {
                    "code": "MAT-001",
                    "name": "上海中洲特种合金材料股份有限公司",
                    "type_name": "原材料",
                    "tech_score": "80分",
                    "arv_rate": "33333",
                    "pp_rate": "4444",
                    "quali_score": "70分",
                    "status": "555555",
                    "pay_type_status": "555555",
                    "quali": "555555",
                    "action": "1111"
                },
                {
                    "code": "MAT-002",
                    "name": "苏州匠心",
                    "type_name": "原材料",
                    "tech_score": "80分",
                    "arv_rate": "33333",
                    "pp_rate": "4444",
                    "quali_score": "70分",
                    "status": "555555",
                    "pay_type_status": "555555",
                    "quali": "555555",
                    "action": "1111"
                }
            ]
        }';
        //return $info;
    }
    public function del(){

    }

    public function add(){

    }

    public function edit(){
        $sup_id = intval(input('param.id'));
        $logicSupInfo = Model('Supporter','logic');
        $sup_info = $logicSupInfo->getOneSupInfo($sup_id);//联合查询得到相关信息
        //dump($sup_info);
        if($sup_info){
            $this->assign('sup_info',$sup_info);
            $supQuali = $logicSupInfo->getSupQuali($sup_info['code']);
            dump($supQuali);
        }
        return view();
    }

    /*
     * 得到技术评分
     */
    public function getTechScore(){
        return '80分';
    }

    /*
     * 供应商资质评分
     */
    public function getQualiScore(){
        return '70分';
    }
}