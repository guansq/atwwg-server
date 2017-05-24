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

class Enquiryorder extends BaseController{
    protected $table = 'Io';
    protected $title = '询价单管理';

    public function index(){
        $this->assign('title',$this->title);
        return view();
    }

    public function getInquiryList(){
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $logicIoInfo = Model('Io','logic');
        $list = $logicIoInfo->getIoList($start,$length);
        $returnArr = [];
        //状态init=未报价  quoted=已报价  winbid=中标 giveupbid=弃标  close=已关闭
        $status = [
            'init' => '初始',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'close' => '关闭',
        ];
        foreach($list as $k => $v){
            if($v['inquiry_way'] == 'assign' && $v['status'] == 'hang'){//订单挂起状态 且询价方式为指定
                $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" class="ver_top checked" value="">指定';
                //选择供应商
                $v['inquiry_way'] = '<a class="select_sell" href="#" data-url="'.url('requireorder/selectSup',array('pr_code'=>$v['pr_code'],'item_code'=>$v['item_code'])).'">选择供应商</a>';
            }else{
                $v['is_appoint_sup'] = '未指定';
                if(in_array($v['inquiry_way'],$inquiry_way)){
                    $v['inquiry_way'] = $inquiry_way[$v['inquiry_way']];
                }else{
                    $v['inquiry_way'] = '其他';
                }
            }
            if(in_array($v['check_status'],$checkStatus)){
                $v['check_status'] = $checkStatus[$v['check_status']];
            }else{
                $v['check_status'] = '未审批';
            }
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
                'status' => $status[$v['status']],//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => $v['pur_attr'],//物料采购属性
                'is_appoint_sup' => $v['is_appoint_sup'],//是否指定供应商
                'inquiry_way' => $v['inquiry_way'],//询价方式
                'check_status' => $v['check_status'],//主管审批
            ];

        }
        $info = ['draw'=>time(),'recordsTotal'=>$logicPrInfo->getListNum(),'recordsFiltered'=>$logicPrInfo->getListNum(),'data'=>$returnArr];

        return json($info);
    }
    public function del(){

    }

    public function add(){

    }

    public function particulars(){
        return view();
    }

}