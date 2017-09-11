<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */

namespace app\admin\controller;

use PHPExcel;
use PHPExcel_IOFactory;
use Qiniu\Auth as QiniuAuth;
use service\HttpService;
use think\Request;

class Order extends BaseController{
    protected $table = 'SystemPo';
    protected $title = '订单管理';

    const MSGPASSTITLE     = '合同审核通过';
    const MSGREFUSETITLE   = '合同审核拒绝';
    const MSGPASSCONTENT   = '合同审核通过';
    const MSGREFUSECONTENT = '合同审核拒绝';

    /*
     * 待下订单列表
     */
    public function wait(){
        $this->title = '待下订单';
        $poLogic = model('Po', 'logic');
        $allNums = $poLogic->getPoItemCount();
        $this->assign('allNums', $allNums);
        //echo $allNums;
        $this->assign('title', $this->title);
        return view();
    }

    /*
     * 采购订单列表
     */
    public function index(){
        $this->title = '采购订单';
        $poLogic = model('Po', 'logic');
        $allNums = $poLogic->getPoCount();
        $orderStatus = array(
            'init' => '待确认',
            //'sup_cancel' => '已取消',
            //'sup_edit' => '修改交期',
            //'atw_sure' => '待上传',
            'sup_sure' => '待上传',
            //'atw_cancel'=>'已取消 ',
            'upload_contract' => '待审核',
            'contract_pass' => '审核通过',
            'contract_refuse' => '审核拒绝',
            'executing' => '执行中',
            'zr_close' => '自然关闭',
            'dq_close' => '短缺关闭',
            'ce_close' => '超额关闭',
        );
        $this->assign('orderstatus', $orderStatus);
        $this->assign('allNums', $allNums);
        $this->assign('title', $this->title);
        return view();
    }

    public function getPoList(){
        $now = time();
        $poLogic = model('Po', 'logic');
        $get = input('param.');
        $where = [];
        // 应用搜索条件
        foreach(['order_code', 'sup_code', 'sup_name', 'status'] as $key){
            if(isset($get[$key]) && $get[$key] !== ''){
                if($key == 'order_code'){
                    $where['po.order_code'] = ['like', "%{$get[$key]}%"];
                }else if($key == 'sup_code'){
                    $where['po.sup_code'] = ['like', "%{$get[$key]}%"];
                }else if($key == 'sup_name'){
                    $where['sup.name'] = ['like', "%{$get[$key]}%"];
                }else if($key == 'status'){
                    $where['po.status'] = $get['status'];
                    if($get['status'] == 'zr_close'){
                        $where['po.status'] = 'finish';
                        $where['po.u9_status'] = 3;
                        continue;
                    }
                    if($get['status'] == 'dq_close'){
                        $where['po.status'] = 'finish';
                        $where['po.u9_status'] = 4;
                        continue;
                    }
                    if($get['status'] == 'ce_close'){
                        $where['po.status'] = 'finish';
                        $where['po.u9_status'] = 5;
                        continue;
                    }
                }

                //$where[$key] = ['like', "%{$get[$key]}%"];
            }
        }
        //逾期状态
        $isCheckExceed = false;
        if(empty($get['status'])){
            $where['po.status'] = ['NOT IN', [ 'sup_cancel', 'atw_cancel']];
        }
        if(!empty($get['status']) && $get['status'] == 'exceed'){
            $where['po.status'] = ['NOT IN', ['finish', 'sup_cancel', 'atw_cancel']];
            $isCheckExceed = true;
        }
        if(!empty($get['status']) && in_array($get['status'], ['sup_cancel', 'sup_sure', 'upload_contract'])){
            $where['po.status'] = $get['status'];
        }
        //dump($where);die;
        $list = $poLogic->getPolist($where);
        //dump($list);
        $retList = [];
        $status = [
            '' => '',
            'init' => '初始',
            'sup_cancel' => '供应商取消',
            'sup_edit' => '供应商修改',
            'atw_sure' => '安特威确定',
            'sup_sure' => '供应商确定/待上传合同',
            'upload_contract' => '供应商已经上传合同',
            'contract_pass' => '合同审核通过',
            'contract_refuse' => '合同审核拒绝',
            'executing' => '执行中',
            'finish' => '结束',
        ];
        //dump(collection($list)->toArray());die;
        foreach($list as $k => $v){

            $exec_desc = '';
            $itemInfo = $poLogic->getPoItemInfo($v['id']);

            $hasExceed = false;
            foreach($itemInfo as $vv){
                $vv['arv_goods_num'] = $vv['arv_goods_num'] == '' ? 0 : $vv['arv_goods_num'];
                $vv['pro_goods_num'] = $vv['pro_goods_num'] == '' ? 0 : $vv['pro_goods_num'];
                //$exec_desc .= '物料名称：'.$vv['item_name'].'; '.'到货数量：'.$vv['arv_goods_num'].'; 未到货数量：'.$vv['pro_goods_num'].'; 可供货交期：'.date('Y-m-d', $vv['sup_confirm_date']).'<br>';
                // 是否有逾期物料
                $hasExceed = $hasExceed || (($vv['sup_confirm_date'] < $now) && ($vv['pro_goods_num'] > 0) && !in_array($v['status'], [
                            'finish',
                            'sup_cancel'
                        ]));
            }

            if((!$hasExceed) && $isCheckExceed){
                continue;
            }
            $statusStr = '';
            switch($v['status']){
                case 'init'://初始
                    $action = [];
                    $statusStr = '待确认';
                    break;
                case 'sup_cancel'://供应商取消
                    $action = [];
                    $statusStr = '已取消';
                    break;
                case 'atw_cancel':// 安特威取消
                    $action = [];
                    $statusStr = '已取消';
                    break;
                case 'sup_edit'://供应商修改
                    $statusStr = '<a href="javascript:;" onclick="verifyOrder('.$v['id'].',\'atw_sure\',this);">供应商修改交期，请确认</a>';
                    break;
                case 'atw_sure'://安特威确定 以及init
                    $statusStr = '待确认';
                    break;
                case 'sup_sure'://供应商确定/待上传合同
                    $statusStr = '待上传';
                    break;
                case 'upload_contract'://供应商已经上传合同
                    $statusStr = '待审核';
                    /*$returnInfo[$k]['status'] = '<a href="javascript:;" onclick="verifyOrder('.$v['id'].',\'contract_pass\',this);">合同审核通过</a>
                    <a href="javascript:;" onclick="verifyOrder('.$v['id'].',\'contract_refuse\',this);">拒绝该合同</a>';*/
                    break;
                case 'contract_pass'://合同审核通过
                    $statusStr = '审核通过';
                    break;
                case 'contract_refuse'://合同审核拒绝
                    $statusStr = '审核拒绝';
                    break;
                case 'executing'://合同审核拒绝
                    $statusStr = '执行中';
                    break;
                case 'finish'://合同审核拒绝 '' => '结束',
                    $statusStr = '关闭';
                    if($v['u9_status'] == 3){
                        $statusStr = '自然关闭';
                    }
                    if($v['u9_status'] == 4){
                        $statusStr = '短缺关闭';
                    }
                    if($v['u9_status'] == 5){
                        $statusStr = '超频关闭';
                    }
                    break;
            }

            $retList[] = [
                'detail' => '<a class="detail" data-open="'.url('order/detailed').'?id='.$v['id'].'">详情</a>',
                'exec_desc' => $exec_desc,
                'id' => $v['id'],
                'checked' => $v['id'],
                'order_code' => $v['order_code'],
                'create_at' => atwDate($v['create_at']),
                'sup_code' => $v['sup_code'],
                'sup_name' => $v['sup_name'],//$poLogic->getSupName($v['sup_code']),
                'status' => empty($v['u9_status']) ? $statusStr : $v['u9_status']
            ];
        }
        return $retList;
    }


    public function getOrderList(){
        $returnInfo = $this->getPoList();
        $info = ['draw' => time(), 'data' => $returnInfo, 'extData' => [],];
        return json($info);
    }

    /*
     * 待下订单列表
     */
    public function getItemList(){
        $poLogic = model('Po', 'logic');
        $get = input('param.');
        $where = [];
        if(isset($get['req_date']) && $get['req_date'] !== ''){
            $get['req_date'] = strtotime($get['req_date']);
        }
        // 应用搜索条件
        foreach(['item_code', 'pr_code', 'sup_name', 'req_date'] as $key){
            if(isset($get[$key]) && $get[$key] !== ''){
                if($key == 'req_date'){
                    $where[$key] = ['between', [$get['req_date'], $get['req_date'] + 86399]];
                }else{
                    $where[$key] = ['like', "%{$get[$key]}%"];
                }
            }
        }
        $itemList = $poLogic->getPoItemList($where);
        $returnInfo = [];
        $itemStatus = [
            '' => '未下单',
            'init' => '未下单',
            'placeorder' => '已下单'
        ];

        foreach($itemList as $k => $v){
            $returnInfo[$k]['checked'] = $v['id'];
            $exec_desc = '';
            $v['arv_goods_num'] = $v['arv_goods_num'] == '' ? 0 : $v['arv_goods_num'];
            $v['pro_goods_num'] = $v['pro_goods_num'] == '' ? 0 : $v['pro_goods_num'];
            //$exec_desc .= '物料名称：'.$v['item_name'].'; '.'到货数量：'.$v['arv_goods_num'].'; 未到货数量：'.$v['pro_goods_num'].'; 可供货交期：'.date('Y-m-d', $v['sup_confirm_date']).'<br>';
            $returnInfo[$k]['exec_desc'] = $exec_desc;
            $returnInfo[$k]['po_id'] = $v['po_id'];//合并订单编号

            //$returnInfo[$k]['po_code'] = '';//U9生成订单编号
            $returnInfo[$k]['pro_no'] = $v['pro_no'];//U9生成订单编号

            $returnInfo[$k]['pr_code'] = $v['pr_code'];//请购单编号
            $returnInfo[$k]['pr_date'] = atwDate($poLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['create_at'] = '';//合并订单日期  date('Y-m-d', $v['create_at'])

            $returnInfo[$k]['sup_name'] = $v['sup_name'];
            $returnInfo[$k]['item_code'] = $v['item_code'];
            $returnInfo[$k]['item_name'] = $v['item_name'];
            $returnInfo[$k]['winbid_time'] = atwDate($v['winbid_time']);
            $returnInfo[$k]['req_date'] = atwDate($v['req_date']);//要求交期
            $returnInfo[$k]['sup_confirm_date'] = atwDate($v['sup_confirm_date']);//承诺交期
            $returnInfo[$k]['tc_num'] = $v['tc_num'];//采购数量
            $returnInfo[$k]['price'] = atwMoney($v['price']);//报价
            $returnInfo[$k]['total_price'] = atwMoney($v['price']*$v['tc_num']);//小计
            $returnInfo[$k]['status'] = $itemStatus[$v['status']];
        }
        //dump($itemList);
        $info = ['draw' => time(), 'data' => $returnInfo, 'extData' => [],];

        return json($info);
    }

    /**
     *
     */
    public function getPiPage(){

        // 申请资源 获取参数
        $poLogic = model('Po', 'logic');
        $reqParams = $this->getReqParams(['start' => 0, 'length' => 10, 'searchKwd' => []]);
        $pageIndex = ceil($reqParams['start']/$reqParams['length']) + 1;
        $piPage = $poLogic->getPoItemPage($reqParams['searchKwd'], $pageIndex, $reqParams['length']);
        $info = [
            'draw' => time(),
            'data' => $piPage->itemList,
            'extData' => $reqParams,
            'recordsTotal' => $piPage->itemTotal,
            'recordsFiltered' => $piPage->itemTotal
        ];
        return json($info);
    }


    public function mkzip(){
        $accessKey = sysconf('storage_qiniu_access_key');
        $secretKey = sysconf('storage_qiniu_secret_key');
        $bucket = sysconf('storage_qiniu_bucket');
        $host = sysconf('storage_qiniu_domain');
        $key = '1.png';
        $auth = new QiniuAuth($accessKey, $secretKey);
    }

    /*
     * po详情
     */
    public function detailed(){
        $today = strtotime(date('Y-m-d'));
        $this->assign('title', $this->title);
        $id = input('get.id');
        //echo $id;
        $poLogic = model('Po', 'logic');
        $poInfo = $poLogic->getPoInfo($id);
        $prLogic = model('RequireOrder', 'logic');
        //dump($id);die;
        //$where = ['pr_code' => $poInfo['pr_code']];

        //$poInfo['pr_date'] = $prLogic->getPrDate($where);
        $supLogic = model('Supporter', 'logic');
        $where = ['code' => $poInfo['sup_code']];
        $poInfo['sup_name'] = $supLogic->getSupName($where);
        $poItemInfo = $poLogic->getPoItemInfo($id);
        $allAmount = 0;
        $hasDoubleUom = 0;
        foreach($poItemInfo as $k => $v){
            //双单位的物料 不计算总价。
            if($v['price_uom'] != $v['tc_uom']){
                $pi['price'] =0;
                $pi['tc_num'] ='';
                $v['amount'] ='实际重量结算';
                $hasDoubleUom = 1;
            }else{
                $allAmount += $v['tc_num']*$v['price'];
            }

            $v['arv_goods_num'] = empty($v['arv_goods_num']) ? 0 : $v['arv_goods_num'];
            $v['pro_goods_num'] = empty($v['pro_goods_num']) ? 0 : $v['pro_goods_num'];
            //送货剩余天数
            //dd(floor(($v['sup_confirm_date']-$now)/(60*60*24)));
            $v['surplus_days'] = in_array($v['status'], [
                'finish',
                'sup_cancel'
            ]) ? 999 : intval(($v['sup_confirm_date'] - $today)/(60*60*24));
        }
        $this->assign('poInfo', $poInfo);
        $this->assign('poItemInfo', $poItemInfo);
        $this->assign('allAmount', $hasDoubleUom? '实际重量结算':$allAmount);
        return view();
    }

    /*
     * po_item 详情
     */
    public function pidetailed(){
        $this->assign('title', $this->title);
        $id = input('get.id');
        //得到item详情
        $poLogic = model('Po', 'logic');
        $poItemInfo = $poLogic->getPoItem($id);
        //添加pr_date
        $prLogic = model('RequireOrder', 'logic');
        $where = ['pr_code' => $poItemInfo['pr_code']];
        $poItemInfo['pr_date'] = $prLogic->getPrDate($where);
        //dump($poItemInfo);die;
        $this->assign('poItemInfo', $poItemInfo);
        return view();
    }

    public function verifyStatus(){
        $poLogic = model('Po', 'logic');
        $logicSupInfo = Model('Supporter', 'logic');
        $param = input('param.');
        $piData = [
            'status' => $param['action'],
            'remark' => input('param.remark')
        ];
        $where = [
            'id' => $param['id'],

        ];

        $status = [
            'atw_sure' => '待供应商确定订单',
            'contract_pass' => '合同已审核通过',
            'contract_refuse' => '合同已被拒绝',

        ];
        $res = $poLogic->saveStatus($where, $piData);

        //$res = true;
        if($res !== false){
            $remark = input('param.remark');
            if($param['action'] == 'contract_pass' || $param['action'] == 'contract_refuse'){

                if($param['action'] == 'contract_pass'){
                    $title = self::MSGPASSTITLE;
                    $content = $remark != '' ? self::MSGPASSCONTENT.'通过原因如下：'.$remark : self::MSGPASSCONTENT;
                }
                if($param['action'] == 'contract_refuse'){
                    $title = self::MSGREFUSETITLE;
                    $content = $remark != '' ? self::MSGREFUSECONTENT.'拒绝原因如下：'.$remark : self::MSGREFUSECONTENT;
                }
                $sendInfo = $logicSupInfo->getSupSendInfo(['code' => input('param.sup_code')]);
                //通过sup_code得到发送信息
                if($sendInfo['phone']){ //发送消息
                    //sendSMS('18451847701',$content);
                    sendSMS($sendInfo['phone'], $content);
                }
                if($sendInfo['email']){ //发送邮件
                    //sendMail('94600115@qq.com',$title,$content);
                    sendMail($sendInfo['email'], $title, $content);
                }
                if($sendInfo['push_token']){ //发送token
                    pushInfo($sendInfo['push_token'], $title, $content);
                }
            }
            if($param['action'] == 'contract_pass'){//已审核通过---》执行同步U9订单

                $poData = [
                    //'order_code' => $res['result']['DocNo'],
                    'status' => 'executing',
                    'contract_time' => time(),
                    'update_at' => time()
                ];
                $res = $poLogic->saveStatus($where, $poData);//订单写入数据库
                //发消息

                if($res !== false){
                    return json(['code' => 2000, 'msg' => '合同审核通过', 'data' => []]);
                }else{
                    return json(['code' => 2000, 'msg' => '合同审核通过', 'data' => []]);
                }
            }
            return json(['code' => 2000, 'msg' => $status[$param['action']], 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }


    /*
     * 导出excel
     */
    function exportExcel(){
        $poLogic = model('Po', 'logic');
        $get = input('param.');
        $where = [];
        if(isset($get['req_date']) && $get['req_date'] !== ''){
            $get['req_date'] = strtotime($get['req_date']);
        }
        // 应用搜索条件
        foreach(['item_code', 'pr_code', 'sup_name', 'req_date'] as $key){
            if(isset($get[$key]) && $get[$key] !== ''){
                if($key == 'req_date'){
                    $where[$key] = ['between', [$get['req_date'], $get['req_date'] + 86399]];
                }else{
                    $where[$key] = ['like', "%{$get[$key]}%"];
                }
            }
        }
        $itemList = $poLogic->getPoItemList($where);
        $returnInfo = [];
        $itemStatus = [
            '' => '未下单',
            'init' => '未下单',
            'placeorder' => '已下单'
        ];

        foreach($itemList as $k => $v){
            $po = $poLogic->getPoById($v['po_id']);

            $returnInfo[$k]['checked'] = $v['id'];
            $exec_desc = '';
            $v['arv_goods_num'] = $v['arv_goods_num'] == '' ? 0 : $v['arv_goods_num'];
            $v['pro_goods_num'] = $v['pro_goods_num'] == '' ? 0 : $v['pro_goods_num'];
            //$exec_desc .= '物料名称：'.$v['item_name'].'; '.'到货数量：'.$v['arv_goods_num'].'; 未到货数量：'.$v['pro_goods_num'].'; 可供货交期：'.date('Y-m-d', $v['sup_confirm_date']).'<br>';
            $returnInfo[$k]['exec_desc'] = $exec_desc;
            $returnInfo[$k]['po_id'] = $v['po_id'];//合并订单编号
            $returnInfo[$k]['pr_code'] = $v['pr_code'];//请购单编号
            $returnInfo[$k]['item_name'] = $v['item_name'];//物料名称
            $returnInfo[$k]['pr_date'] = atwDate($poLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['pro_no'] = $po['pro_no'];
            $returnInfo[$k]['create_at'] = '';//合并订单日期  date('Y-m-d', $v['create_at'])
            $returnInfo[$k]['sup_name'] = $v['sup_name'];
            $returnInfo[$k]['item_code'] = $v['item_code'];
            $returnInfo[$k]['winbid_time'] = atwDate($v['winbid_time']);
            $returnInfo[$k]['req_date'] = atwDate($v['req_date']);//要求交期
            $returnInfo[$k]['sup_confirm_date'] = atwDate($v['sup_confirm_date']);//承诺交期
            $returnInfo[$k]['tc_num'] = $v['tc_num'];//采购数量
            $returnInfo[$k]['price'] = atwMoney($v['price']);//报价
            $returnInfo[$k]['total_price'] = atwMoney($v['price']*$v['tc_num']);//小计
            $returnInfo[$k]['status'] = $itemStatus[$v['status']];
        }


        $list = $returnInfo;
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        //dump($list);die;请购单编号-物料编号-请购日期-评标日期-供应商名称-要求交期-承诺交期-采购数量-报价-小计-状态
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('待下订单列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValueExplicit('A1', '请购单编号');
        $PHPSheet->setCellValueExplicit('B1', '物料编号');
        $PHPSheet->setCellValueExplicit('C1', '物料描述');
        $PHPSheet->setCellValueExplicit('D1', '项目号');
        $PHPSheet->setCellValueExplicit('E1', '请购日期');
        $PHPSheet->setCellValueExplicit('F1', '评标日期');
        $PHPSheet->setCellValueExplicit('G1', '供应商名称');
        $PHPSheet->setCellValueExplicit('H1', '要求交期');
        $PHPSheet->setCellValueExplicit('I1', '承诺交期');
        $PHPSheet->setCellValueExplicit('J1', '采购数量');
        $PHPSheet->setCellValueExplicit('K1', '报价');
        $PHPSheet->setCellValueExplicit('L1', '小计');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num + 1;
            $PHPSheet->setCellValueExplicit('A'.$num, $v['pr_code'])
                ->setCellValueExplicit('B'.$num, $v['item_code'])
                ->setCellValueExplicit('C'.$num, $v['item_name'])
                ->setCellValueExplicit('D'.$num, $v['pro_no'])
                ->setCellValueExplicit('E'.$num, $v['pr_date'])
                ->setCellValueExplicit('F'.$num, $v['winbid_time'])
                ->setCellValueExplicit('G'.$num, $v['sup_name'])
                ->setCellValueExplicit('H'.$num, $v['req_date'])
                ->setCellValueExplicit('I'.$num, $v['sup_confirm_date'])
                ->setCellValueExplicit('J'.$num, $v['tc_num'])
                ->setCellValueExplicit('K'.$num, $v['price'])
                ->setCellValueExplicit('L'.$num, $v['total_price']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/poItemList.xlsx'); //表示在$path路径下面生成ioList.xlsx文件
        $file_name = "未下单采购订单".date('Y-m-d', time()).".xlsx";
        $contents = file_get_contents($path.'/poItemList.xlsx');
        $file_size = filesize($path.'/poItemList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 同步PO执行状态
     */
    public function syncErp(){
        $httpRet = HttpService::get(getenv('APP_API_HOME').'/u9api/syncPOState');
        $httpRet = json_decode($httpRet, true);
        if(empty($httpRet)){
            returnJson(6000);
        }

        returnJson($httpRet);
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 同步PO执行状态
     */
    public function syncPO(){
        $httpRet = HttpService::get(getenv('APP_API_HOME').'/u9api/syncPo');
        $httpRet = json_decode($httpRet, true);
        if(empty($httpRet)){
            returnJson(6000);
        }

        returnJson($httpRet);
    }

    /*
     * 合并生成订单
     */
    public function placeOrderByPoItem(){
        $now = time();
        $ids = input('param.ids');
        $idArr = explode('|', $ids);
        $poLogic = model('Po', 'logic');
        $supCodeInfo = [];
        if(empty($ids)){
            return json(['code' => 4000, 'msg' => '请选择单据', 'data' => []]);
        }

        $poArr = [];
        foreach($idArr as $k => $v){
            $po_id = $poLogic->getPoId(['id' => $v]);//判断id是否存在po_id有存在返回不能合并订单操作
            if($po_id){
                $poArr[$k] = $po_id;
            }
            $supInfo = $poLogic->getSupInfo(['id' => $v]);//通过id获取sup_code sup_name
            $supCodeInfo[$supInfo['sup_code']] = $supInfo['sup_name'];
        }

        if(!empty($poArr)){
            return json(['code' => 4000, 'msg' => '抱歉，您选择的采购订单中已经存在下单后的订单状态', 'data' => []]);
        }
        if(count($supCodeInfo) != 1){
            return json(['code' => 4000, 'msg' => '抱歉，您选择的采购订单中包含多家供应商或采购订单中供应商已不存在', 'data' => []]);
        }
        foreach($supCodeInfo as $k => $v){
            $sup_code = $k;
            $sup_name = $v;
        }

        return returnJson($poLogic->placePoOrder($idArr, $sup_code));

    }

    /*
     * 导出excel采购订单列表
     */
    public function exportPoExcel(){
        $list = $this->getPoList();
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        //dump($list);die;请购单编号-物料编号-请购日期-评标日期-供应商名称-要求交期-承诺交期-采购数量-报价-小计-状态
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('采购订单列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValueExplicit('A1', '订单编号');
        $PHPSheet->setCellValueExplicit('B1', '下单日期');
        $PHPSheet->setCellValueExplicit('C1', '供应商名称');
        $PHPSheet->setCellValueExplicit('D1', '状态');
        $PHPSheet->setCellValueExplicit('E1', '执行情况');
        $num = 1;
        foreach($list as $k => $v){
            $v['exec_desc'] = str_replace('<br>', "\r\n", $v['exec_desc']);
            $num = $num + 1;
            $PHPSheet->setCellValueExplicit('A'.$num, $v['order_code'])
                ->setCellValueExplicit('B'.$num, $v['create_at'])
                ->setCellValueExplicit('C'.$num, $v['sup_name'])
                ->setCellValueExplicit('D'.$num, $v['status'])
                ->setCellValueExplicit('E'.$num, $v['exec_desc']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/poItemList.xlsx'); //表示在$path路径下面生成ioList.xlsx文件
        $file_name = "已下单采购订单".date('Y-m-d', time()).".xlsx";
        $contents = file_get_contents($path.'/poItemList.xlsx');
        $file_size = filesize($path.'/poItemList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

    /*
     * 导出excel采购订单列表
     */
    public function exportPiList(){

        // 申请资源 获取参数
        $poLogic = model('Po', 'logic');
        $reqParams = $this->getReqParams(['pr' => '', 'po' => '', 'item' => '', 'sup' => '', 'pro' => '']);

        $piPage = $poLogic->getPoItemPage($reqParams, 1, PHP_INT_MAX);
        $list = $piPage->getItemList();

        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        //dump($list);die;请购单编号-物料编号-请购日期-评标日期-供应商名称-要求交期-承诺交期-采购数量-报价-小计-状态
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        foreach(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'] as $cl){
            //設置列寬自適應
            $PHPSheet->getColumnDimension($cl)->setAutoSize(true);
        }
        $PHPSheet->setTitle('采购订单列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValueExplicit('A1', '采购订单号');
        $PHPSheet->setCellValueExplicit('B1', '请购订单号');
        $PHPSheet->setCellValueExplicit('C1', '物料编号');
        $PHPSheet->setCellValueExplicit('D1', '物料描述');
        $PHPSheet->setCellValueExplicit('E1', '供应商编号');
        $PHPSheet->setCellValueExplicit('F1', '供应商名称');
        $PHPSheet->setCellValueExplicit('G1', '要求交期');
        $PHPSheet->setCellValueExplicit('H1', '承诺交期');
        $PHPSheet->setCellValueExplicit('I1', '修改交期');
        $PHPSheet->setCellValueExplicit('J1', '采购数量');
        $PHPSheet->setCellValueExplicit('K1', '单价');
        $PHPSheet->setCellValueExplicit('L1', '交易单位');
        $PHPSheet->setCellValueExplicit('M1', '小计');
        $PHPSheet->setCellValueExplicit('N1', '到货数量');
        $PHPSheet->setCellValueExplicit('O1', '未交数量');
        $PHPSheet->setCellValueExplicit('P1', '退货数量');
        $PHPSheet->setCellValueExplicit('Q1', '采购员');
        $PHPSheet->setCellValueExplicit('R1', '项目号');
        $PHPSheet->setCellValueExplicit('S1', '状态');
        $num = 1;
        foreach($list as $k => $v){
            $num++;
            $PHPSheet->setCellValueExplicit('A'.$num, $v['po_code'])
                ->setCellValueExplicit('B'.$num, $v['pr_code'])
                ->setCellValueExplicit('C'.$num, $v['item_code'])
                ->setCellValueExplicit('D'.$num, $v['item_name'])
                ->setCellValueExplicit('E'.$num, $v['sup_code'])
                ->setCellValueExplicit('F'.$num, $v['sup_name'])
                ->setCellValueExplicit('G'.$num, $v['req_date_fmt'])
                ->setCellValueExplicit('H'.$num, $v['sup_confirm_date_fmt'])
                ->setCellValueExplicit('I'.$num, $v['sup_update_date_fmt'])
                ->setCellValueExplicit('J'.$num, $v['tc_num_fmt'])
                ->setCellValueExplicit('K'.$num, $v['price_fmt'])//$v['price_fmt']
                ->setCellValueExplicit('L'.$num, $v['tc_uom'])
                ->setCellValueExplicit('M'.$num, $v['price_subtotal_fmt'])
                ->setCellValueExplicit('N'.$num, $v['arv_goods_num_fmt'])
                ->setCellValueExplicit('O'.$num, $v['pro_goods_num_fmt'])
                ->setCellValueExplicit('P'.$num, $v['return_goods_num'])
                ->setCellValueExplicit('Q'.$num, $v['purch_name'])
                ->setCellValueExplicit('R'.$num, $v['pro_no'])
                ->setCellValueExplicit('S'.$num, empty($v['u9_status'])?'执行中':$v['u9_status']);
            //  echo $v['price_fmt'].'<br/>';
        }

        //        dd(1);

        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/poItemList.xlsx'); //表示在$path路径下面生成ioList.xlsx文件
        $file_name = "单采购订单报表".date('Y-m-d', time()).".xlsx";
        $contents = file_get_contents($path.'/poItemList.xlsx');
        $file_size = filesize($path.'/poItemList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 取消订单
     */
    public function cancelPo(Request $request){
        $reqParams = $this->getReqParams(['poCodes' => [], 'cancelCause' => '']);
        if(empty($reqParams['poCodes'])){
            returnJson(4001);
        }
        $poLogic = model('Po', 'logic');
        returnJson($poLogic->cancelPo($reqParams['poCodes'], $reqParams['cancelCause']));
    }
}