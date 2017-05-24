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
            //得到全部的询价单 by pr_code item_code
            $where = [
                '' => '',
                '' => '',
                '' => ''
            ];

            //得到已报价的询价单by pr_code item_code status
            $where = [
                '' => '',
                '' => '',
                '' => ''
            ];
            $returnArr[] = [
                'pr_code' => $v['pr_code'],//询价单号
                'pr_date' => $v['pr_date'],//请购单号
                'item_code' => $v['item_code'],//料号
                'desc' => $v['desc'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => $v['req_date'],//交期
                'quote_date' => $v['quote_date'],//询价日期
                'quote_endtime' => $v['quote_endtime'],//报价截止日期
                //报价状态
                'status' => $status[$v['status']],//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => $v['pur_attr'],//详情
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