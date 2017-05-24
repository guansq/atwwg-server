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

class Requireorder extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '请购单管理';

    public function index(){
        //echo '111111111';die;
        $this->assign('title',$this->title);
        return view();
    }

    public function getPrList(){
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $logicPrInfo = Model('RequireOrder','logic');
        $list = $logicPrInfo->getPrList($start,$length);
        $returnArr = [];
        //dump($list);die;
        foreach($list as $k => $v){
            $returnArr[] = [
                'pr_code' => $v['pr_code'],//请购单号
                'pr_date' => $v['pr_date'],//请购日期
                'item_code' => $v['item_code'],//料号
                'desc' => $v['desc'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => $v['req_date'],//交期
                'status' => $v['status'],//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => $v['pur_attr'],//物料采购属性
                'pur_attr' => $v['pur_attr'],//是否指定供应商
                'pur_attr' => $v['pur_attr'],//询价方式
                'pur_attr' => $v['pur_attr'],//主管审批
            ];

        }
        $info = ['draw'=>time(),'recordsTotal'=>$logicItemInfo->getListNum(),'recordsFiltered'=>$logicItemInfo->getListNum(),'data'=>$returnArr];

        return json($info);
    }
    public function del(){

    }

    public function add(){

    }

}