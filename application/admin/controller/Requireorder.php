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
            'init' => '待询价',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'close' => '关闭',
        ];

        $checkStatus = [
            '' => '未审批',
            'agree' => '已同意',
            'refuse' => '已拒绝',
        ];

        $inquiry_way = [
            '' => '自动询价',
            'assign' => '指定供应商',
            'exclusive' => '独家采购',
            'compete' => '充分竞争'
        ];
        //dump($list);die;
        foreach($list as $k => $v){
            if($v['inquiry_way'] == 'assign' && $v['status'] == 'hang'){//订单挂起状态 且询价方式为指定
                $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_id="'.$v['id'].'" data-pr_code="'.$v['pr_code'].'" data-item_code="'.$v['item_code'].'" class="ver_top" checked value="1">指定';
                if(!empty($v['appoint_sup_code'])){
                    $inquiry = $v['appoint_sup_name'];//可以进行主管审批操作
                    if($v['check_status'] == ''){
                        //auth();
                        $v['check_status'] =  '<a href="javascript:;" onclick="checkStatus(\'agree\','.$v['id'].');">通过</a>&nbsp;&nbsp;<a href="javascript:;" onclick="checkStatus(\'refuse\','.$v['id'].');">拒绝</a>';
                    }else{
                        if(key_exists($v['check_status'],$checkStatus)){
                            $v['check_status'] = $checkStatus[$v['check_status']];
                        }
                    }
                }else{
                    //选择供应商
                    $inquiry = '<a class="select_sell" href="javascript:void(0);" onclick="bomb_box(event,\''.$v['pr_code'].'\',\''.$v['item_code'].'\',\''.$v['id'].'\');" data-url="'.url('requireorder/selectSup',array('pr_code'=>$v['pr_code'],'item_code'=>$v['item_code'])).'">选择供应商</a>';
                    $v['check_status'] = '';
                }
            }else{
                $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_id="'.$v['id'].'"  data-pr_code="'.$v['pr_code'].'" data-item_code="'.$v['item_code'].'" class="ver_top" value="1">指定';
                $v['check_status'] = '';
                if(key_exists($v['inquiry_way'],$inquiry_way)){
                    $inquiry = $inquiry_way[$v['inquiry_way']];
                }else{
                    //$v['inquiry_way'] = $inquiry_way[$v['inquiry_way']];
                    //$v['inquiry_way'] = '其他';
                    $inquiry = $v['inquiry_way'];
                }
            }

            /*if($v['inquiry_way'] == 'assign'){

            }else{
                $v['check_status'] = '';
            }*/
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
                'status' => key_exists($v['status'],$status) ? $status[$v['status']] : $v['status'],//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => $v['pur_attr'],//物料采购属性
                'is_appoint_sup' => $v['is_appoint_sup'],//是否指定供应商
                'inquiry_way' => $inquiry,//询价方式
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
        $result = $this->validate($data,'Enquiry');
        if($result !== true){
            return json(['code'=>4000,'msg'=>$result,'data'=>[]]);
        }
        $logicPrInfo = Model('RequireOrder','logic');
        $list = $logicPrInfo->getSupList($data['item_code']);
        foreach($list as $k=>$v){
            $list[$k]['pr_code'] = $data['pr_code'];
        }
        return json($list);
    }

    /*
     * 更改询价状态
     */
    public function changeInquiType(){
        $data=input('param.');
        $logicPrInfo = Model('RequireOrder','logic');

        if($data['is_appoint_sup'] == 0){
            $dataArr = [
                'status' => '',
                'inquiry_way' => '',
                'is_appoint_sup' => 0,
                'appoint_sup_code' => '',
                'appoint_sup_name' => '',
            ];

        }else if($data['is_appoint_sup'] == 1){
            $dataArr = [
                'status' => 'hang',
                'inquiry_way' => 'assign',
                'is_appoint_sup' => 1,
            ];
        }else{
            return json(['code'=>4000,'msg'=>'请传入合法的is_appoint_sup参数值','data'=>[]]);
        }
        $where = [
            'pr_code' => $data['pr_code'],
            'item_code' => $data['item_code'],
        ];
        $result = $logicPrInfo->updateByPrCode($where,$dataArr);
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
        $dataArr = [
            'is_appoint_sup' => $data['is_appoint_sup'],
            'appoint_sup_code' => $data['appoint_sup_code'],
            'appoint_sup_name' => $data['appoint_sup_name'],
        ];
        $where = [
            'pr_code' => $data['pr_code'],
            'item_code' => $data['item_code'],
        ];
        $result = $logicPrInfo->updateByPrCode($where,$dataArr);
        if (false === $result) {
            return json(['code'=>4000,'msg'=>'指定供应商状态失败','data'=>[]]);
        }
        //重新计算价格

        return json(['code'=>2000,'msg'=>'成功','data'=>['sup_name' => $data['appoint_sup_name']]]);
        //is_appoint_sup 1 //appoint_sup_code //appoint_sup_name
    }

    /*
     * 审批指定供应商状态
     */
    public function checkStatus(){
        $data=input('param.');
        $logicPrInfo = Model('RequireOrder','logic');
        $dataArr = [
            'check_status' => $data['check_status'],
        ];
        $where = [
            'id' => $data['id'],
        ];
        $checkStatus = [
            '' => '未审批',
            'agree' => '已同意',
            'refuse' => '已拒绝',
        ];
        $result = $logicPrInfo->updateByPrCode($where,$dataArr);
        if (false === $result) {
            return json(['code'=>4000,'msg'=>'失败','data'=>[]]);
        }else{
            $status = $checkStatus[$data['check_status']];
            return json(['code'=>2000,'msg'=>'成功','data'=>['check_status'=>$status]]);
        }
    }

    //public function agree
    public function del(){

    }

    public function add(){

    }

}