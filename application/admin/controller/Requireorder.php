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
        $status = [
            'init' => '初始',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'close' => '关闭',
        ];
        $checkStatus = [
            'agree' => '同意',
            'refuse' => '拒绝',
        ];

        $inquiry_way = [
            'assign' => '指定供应商',
            'exclusive' => '独家采购',
            'compete' => '充分竞争',
        ];
        //
        foreach($list as $k => $v){
            if($v['inquiry_way'] == 'assign' && $v['status'] == 'hang'){//订单挂起状态 且询价方式为指定
                $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_code="'.$v['pr_code'].'" data-item_code="'.$v['item_code'].'" class="ver_top" checked value="1">指定';
                if(!empty($v['appoint_sup_code'])){
                    $v['inquiry_way'] = $v['appoint_sup_name'];
                }else{
                    //选择供应商
                    $v['inquiry_way'] = '<a class="select_sell" href="#" onclick="bomb_box();" data-url="'.url('requireorder/selectSup',array('pr_code'=>$v['pr_code'],'item_code'=>$v['item_code'])).'">选择供应商</a>';
                }
            }else{
                $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_code="'.$v['pr_code'].'" data-item_code="'.$v['item_code'].'" class="ver_top" value="1">指定';
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

    /*
     * 选择物料所对应的多个供应商
     */
    public function showSelectSup(){
        $data=input('param.');
        $result = $this->validate($data,'Banner');
        if($result !== true){
            return json(['code'=>4000,'msg'=>$result,'data'=>[]]);
        }
        $logicPrInfo = Model('RequireOrder','logic');
        $list = $logicPrInfo->getSupList($data['pr_code'],$data['item_code']);
        return json($list);
    }

    /*
     * 更改询价状态
     */
    public function changeInquiType(){
        $data=input('param.');
        $logicPrInfo = Model('RequireOrder','logic');

        if($data['is_appoint_sup'] == 0){
            $where = [
                'status' => '',
                'inquiry_way' => 'init',
                'is_appoint_sup' => 0,
            ];

        }else if($data['is_appoint_sup'] == 1){
            $where = [
                'status' => 'hang',
                'inquiry_way' => 'assign',
                'is_appoint_sup' => 1,
            ];
        }else{
            return json(['code'=>4000,'msg'=>'请传入合法的is_appoint_sup参数值','data'=>[]]);
        }
        $result = $logicPrInfo->updateByPrCode($data['pr_code'],$where);
        if (false === $result) {
            return json(['code'=>4000,'msg'=>'更改指定供应商状态失败','data'=>[]]);
        }
        return json(['code'=>2000,'msg'=>'成功','data'=>[]]);
    }

    /*
     * 保存到pr表
     */
    public function savePr(){
        $data=input('param.');
        $logicPrInfo = Model('RequireOrder','logic');
        $where = [
            'is_appoint_sup' => $data['is_appoint_sup'],
            'appoint_sup_code' => $data['appoint_sup_code'],
            'appoint_sup_name' => $data['appoint_sup_name'],
        ];
        $result = $logicPrInfo->updateByPrCode($data['pr_code'],$where);
        if (false === $result) {
            return json(['code'=>4000,'msg'=>'指定供应商状态失败','data'=>[]]);
        }
        return json(['code'=>2000,'msg'=>'成功','data'=>['sup_name' => $data['appoint_sup_name']]]);
        //is_appoint_sup 1 //appoint_sup_code //appoint_sup_name
    }

    /*
     * 审批指定供应商状态
     */
    public function checkStatus(){

    }
    //public function agree
    public function del(){

    }

    public function add(){

    }

}