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

class Order extends BaseController{
    protected $table = 'SystemPo';
    protected $title = '订单管理';

    /*
     * 待下订单列表
     */
    public function wait(){
        $this->title = '待下订单';
        $this->assign('title', $this->title);
        return view();
    }
    /*
     * 采购订单列表
     */
    public function index(){
        $this->title = '采购订单';
        $this->assign('title', $this->title);
        return view();
    }

    public function getOrderList(){
        $poLogic = model('Po', 'logic');
        $get = input('param.');
        //dump($requestInfo);die;
        $where = [];
        // 应用搜索条件
        foreach(['order_code', 'pr_code'] as $key){
            if(isset($get[$key]) && $get[$key] !== ''){
                $where[$key] = ['like', "%{$get[$key]}%"];
            }
        }
        $list = $poLogic->getPolist($where);
        $returnInfo = [];
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

        foreach($list as $k => $v){
            $returnInfo[$k]['checked'] = $v['id'];
            $exec_desc = '';
            if(!empty($itemInfo = $poLogic->getPoItemInfo($v['id']))){
                foreach($itemInfo as $vv){
                    $vv['arv_goods_num'] = $vv['arv_goods_num'] == '' ? 0 : $vv['arv_goods_num'];
                    $vv['pro_goods_num'] = $vv['pro_goods_num'] == '' ? 0 : $vv['pro_goods_num'];
                    $exec_desc .= '物料名称：'.$vv['item_name'].'; '.'到货数量：'.$vv['arv_goods_num'].'; 未到货数量：'.$vv['pro_goods_num'].'; 可供货交期：'.date('Y-m-d', $vv['sup_confirm_date']).'<br>';
                }
                $returnInfo[$k]['exec_desc'] = $exec_desc;
            }else{
                $returnInfo[$k]['exec_desc'] = '';
            }

            $returnInfo[$k]['order_code'] = $v['order_code'];
            $returnInfo[$k]['pr_code'] = $v['pr_code'];
            $returnInfo[$k]['pr_date'] = date('Y-m-d', $poLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['create_at'] = date('Y-m-d', $v['create_at']);
            $returnInfo[$k]['sup_name'] = $poLogic->getSupName($v['sup_code']);
            $returnInfo[$k]['status'] = empty($v['u9_status']) ? $status[$v['status']] : $v['u9_status'];
            switch($v['status']){
                case 'init'://初始
                    $action = [];
                    $returnInfo[$k]['status'] = '待供应商确定订单';
                    break;
                case 'sup_cancel'://供应商取消
                    $action = [];
                    $returnInfo[$k]['status'] = '供应商取消了订单';
                    break;
                case 'sup_edit'://供应商修改
                    $returnInfo[$k]['status'] = '<a href="javascript:;" onclick="verifyOrder('.$v['id'].',\'atw_sure\',this);">供应商修改，确定订单</a>';
                    break;
                case 'atw_sure'://安特威确定 以及init
                    $returnInfo[$k]['status'] = '待供应商确定订单';
                    break;
                case 'sup_sure'://供应商确定/待上传合同
                    $returnInfo[$k]['status'] = '供应商确定/待上传合同';
                    break;
                case 'upload_contract'://供应商已经上传合同
                    $returnInfo[$k]['status'] = '合同待审核';
                    /*$returnInfo[$k]['status'] = '<a href="javascript:;" onclick="verifyOrder('.$v['id'].',\'contract_pass\',this);">合同审核通过</a>
                                                 <a href="javascript:;" onclick="verifyOrder('.$v['id'].',\'contract_refuse\',this);">拒绝该合同</a>';*/
                    break;
                case 'contract_pass'://合同审核通过
                    $returnInfo[$k]['status'] = '合同审核通过';
                    break;
                case 'contract_refuse'://合同审核拒绝
                    $returnInfo[$k]['status'] = '合同已被拒绝';
                    break;
            }

            $returnInfo[$k]['detail'] = $v['id'];
        }
        //dump($returnInfo);
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
                    $where[$key] = ['between',[$get['req_date'],$get['req_date']+86399]];
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

            $returnInfo[$k]['pr_code'] = $v['pr_code'];//请购单编号
            $returnInfo[$k]['pr_date'] = atwDate($poLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['create_at'] = '';//合并订单日期  date('Y-m-d', $v['create_at'])

            $returnInfo[$k]['sup_name'] = $v['sup_name'];
            $returnInfo[$k]['item_code'] = $v['item_code'];
            $returnInfo[$k]['winbid_time'] = atwDate($v['winbid_time']);
            $returnInfo[$k]['req_date'] = atwDate($v['req_date']);//要求交期
            $returnInfo[$k]['sup_confirm_date'] = atwDate($v['sup_confirm_date']);//承诺交期
            $returnInfo[$k]['price_num'] = $v['price_num'];//采购数量
            $returnInfo[$k]['price'] = atwMoney($v['price']);//报价
            $returnInfo[$k]['total_price'] = atwMoney($v['price']*$v['price_num']);//小计
            $returnInfo[$k]['status'] = $itemStatus[$v['status']];
        }
        //dump($itemList);
        $info = ['draw' => time(), 'data' => $returnInfo, 'extData' => [],];

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
        $this->assign('title', $this->title);
        $id = input('get.id');
        //echo $id;
        $poLogic = model('Po', 'logic');
        $poInfo = $poLogic->getPoInfo($id);
        $prLogic = model('RequireOrder', 'logic');
        //dump($id);die;
        $where = ['pr_code' => $poInfo['pr_code']];
        if($poInfo['status'] == 'upload_contract'){//供应商已经上传合同

        }
        $poInfo['pr_date'] = $prLogic->getPrDate($where);
        $supLogic = model('Supporter', 'logic');
        $where = ['code' => $poInfo['sup_code']];
        $poInfo['sup_name'] = $supLogic->getSupName($where);
        $poItemInfo = $poLogic->getPoItemInfo($id);
        $allAmount = 0;
        foreach($poItemInfo as $k => $v){
            $allAmount += $v['amount'];
        }
        $this->assign('poInfo', $poInfo);
        $this->assign('poItemInfo', $poItemInfo);
        $this->assign('allAmount', $allAmount);
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
        $poItemInfo  = $poLogic->getPoItem($id);
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
        $param = input('param.');
        $piData = [
            'status' => $param['action']
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
            if($param['action'] == 'contract_pass'){//已审核通过---》执行同步U9订单
                $sendData = [];
                $poInfo = $poLogic->getPoInfo($param['id']);

                $sendData['DocDate'] = $poInfo['doc_date'] == '' ? time() : $poInfo['doc_date'];//单价日期
                $sendData['DocTypeCode'] = $poInfo['doc_type'];//单据类型
                $sendData['TCCode'] = $poInfo['tc_code'];//币种编码
                $sendData['bizType'] = $poInfo['biz_type'];//U9参数
                $sendData['isPriceIncludeTax'] = $poInfo['is_include_tax'];//是否含税
                $sendData['supplierCode'] = $poInfo['sup_code'];//供应商代码

                $poItemInfo = $poLogic->getPoItemInfo($poInfo['id']);
                //dump($poItemInfo);die;
                foreach($poItemInfo as $k => $v){
                    $sendData['lines'][$k]['ItemCode'] = $v['item_code'];//料品号
                    $sendData['lines'][$k]['OrderPriceTC'] = $v['price'];//采购单价
                    $sendData['lines'][$k]['OrderTotalTC'] = $v['price']*$v['price_num'];//采购总金额
                    $sendData['lines'][$k]['ReqQty'] = $v['price_num'];//采购数量
                    $sendData['lines'][$k]['RequireDate'] = $v['req_date'];//请购时间
                    $sendData['lines'][$k]['SupConfirmDate'] = $v['sup_confirm_date'];//供应商供货日期
                    $sendData['lines'][$k]['TaxRate'] = $v['tax_rate']*100;//税率
                    $sendData['lines'][$k]['TradeUOM'] = $v['tc_uom'];//交易单位
                    $sendData['lines'][$k]['ValuationQty'] = $v['tc_num'];//
                    $sendData['lines'][$k]['ValuationUnit'] = $v['price_uom'];//
                    $sendData['lines'][$k]['srcDocPRLineNo'] = $v['pr_ln'];
                    $sendData['lines'][$k]['srcDocPRNo'] = $v['pr_code'];
                }
                //dump($sendData);die;
                $httpRet = HttpService::curl(getenv('APP_API_U9').'index/po', $sendData);
                $res = json_decode($httpRet, true);//成功回写数据库
                if($res['code'] != 2000){
                    returnJson($res);
                }
                $where = [
                    'id' => $param['id'],
                ];
                //dd($res['result']);
                $poData = [
                    'order_code' => $res['result']['DocNo'],
                    'status' => 'executing',
                    'update_at' => time()
                ];
                $res = $poLogic->saveStatus($where, $poData);//订单写入数据库
                $where = [
                    'po_id' => $param['id'],
                ];
                $piData = [
                    'update_at' =>time(),
                    'po_code' => $poData['order_code'],
                ];
                $poLogic->saveItemInfo($where,$piData);//更新时间

                if($res !== false){
                    return json(['code' => 2000, 'msg' => '合同审核通过，U9已生成订单', 'data' => []]);
                }else{
                    return json(['code' => 2000, 'msg' => '合同审核通过，U9生成订单失败', 'data' => []]);
                }
            }
            return json(['code' => 2000, 'msg' => $status[$param['action']], 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    public function placeOrder(){
        $id = input('id');
        $poLogic = model('Po', 'logic');
        $sendData = [];
        $poInfo = $poLogic->getPoInfo($id);

        $sendData['DocDate'] = $poInfo['doc_date'] == '' ? time() : $poInfo['doc_date'];//单价日期
        $sendData['DocTypeCode'] = $poInfo['doc_type'];//单据类型
        $sendData['TCCode'] = $poInfo['tc_code'];//币种编码
        $sendData['bizType'] = $poInfo['biz_type'];//U9参数
        $sendData['isPriceIncludeTax'] = $poInfo['is_include_tax'];//是否含税
        $sendData['supplierCode'] = $poInfo['sup_code'];//供应商代码
        $poItemInfo = $poLogic->getPoItemInfo($poInfo['id']);
        //dump($poItemInfo);die;
        $lines = [];
        foreach($poItemInfo as $k => $v){
            $lines[] = [
                'ItemCode' => $v['item_code'],//料品号
                'OrderPriceTC' => $v['price'],//采购单价
                'OrderTotalTC' => $v['price']*$v['price_num'],//采购总金额
                'ReqQty' => $v['price_num'],//采购数量
                'RequireDate' => $v['req_date'],//请购时间
                'SupConfirmDate' => $v['sup_confirm_date'],//供应商供货日期
                'TaxRate' => $v['tax_rate']*100,//税率
                'TradeUOM' => $v['tc_uom'],//交易单位
                'ValuationQty' => $v['tc_num'],//
                'ValuationUnit' => $v['price_uom'],//
                'srcDocPRLineNo' => $v['pr_ln'],
                'srcDocPRNo' => $v['pr_code']
            ];
        }
        $sendData['lines'] = $lines;
        //exit(json_encode($sendData));
        $httpRet = HttpService::curl(getenv('APP_API_U9').'index/po', $sendData);
        $res = json_decode($httpRet, true);//成功回写数据库
        if($res['code'] != 2000){
            returnJson(6000, '调用U9接口异常', $res);
        }
        $where = [
            'id' => $id,
        ];
        $data = [
            'order_code' => $res['result']['DocNo'],
            'status' => 'executing',
        ];
        $res = $poLogic->saveStatus($where, $data);//订单写入数据库
        returnJson(2000);
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
                    $where[$key] = ['between',[$get['req_date'],$get['req_date']+86399]];
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
            $returnInfo[$k]['pr_code'] = $v['pr_code'];//请购单编号
            $returnInfo[$k]['pr_date'] = atwDate($poLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['create_at'] = '';//合并订单日期  date('Y-m-d', $v['create_at'])
            $returnInfo[$k]['sup_name'] = $v['sup_name'];
            $returnInfo[$k]['item_code'] = $v['item_code'];
            $returnInfo[$k]['winbid_time'] = atwDate($v['winbid_time']);
            $returnInfo[$k]['req_date'] = atwDate($v['req_date']);//要求交期
            $returnInfo[$k]['sup_confirm_date'] = atwDate($v['sup_confirm_date']);//承诺交期
            $returnInfo[$k]['price_num'] = $v['price_num'];//采购数量
            $returnInfo[$k]['price'] = atwMoney($v['price']);//报价
            $returnInfo[$k]['total_price'] = atwMoney($v['price']*$v['price_num']);//小计
            $returnInfo[$k]['status'] = $itemStatus[$v['status']];
        }


        $list = $returnInfo;
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        //dump($list);die;请购单编号-物料编号-请购日期-评标日期-供应商名称-要求交期-承诺交期-采购数量-报价-小计-状态
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('待下订单列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '请购单编号');
        $PHPSheet->setCellValue('B1', '物料编号');
        $PHPSheet->setCellValue('C1', '请购日期');
        $PHPSheet->setCellValue('D1', '评标日期');
        $PHPSheet->setCellValue('E1', '供应商名称');
        $PHPSheet->setCellValue('F1', '要求交期');
        $PHPSheet->setCellValue('G1', '承诺交期');
        $PHPSheet->setCellValue('H1', '采购数量');
        $PHPSheet->setCellValue('I1', '报价');
        $PHPSheet->setCellValue('J1', '小计');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num + 1;
            $PHPSheet->setCellValue('A'.$num, $v['pr_code'])
                ->setCellValue('B'.$num, $v['item_code'])
                ->setCellValue('C'.$num, $v['pr_date'])
                ->setCellValue('D'.$num, $v['winbid_time'])
                ->setCellValue('E'.$num, $v['sup_name'])
                ->setCellValue('F'.$num, $v['req_date'])
                ->setCellValue('G'.$num, $v['sup_confirm_date'])
                ->setCellValue('H'.$num, $v['price_num'])
                ->setCellValue('I'.$num, $v['price'])
                ->setCellValue('J'.$num, $v['total_price']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/poItemList.xlsx'); //表示在$path路径下面生成ioList.xlsx文件
        $file_name = "poItemList.xlsx";
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
        if(empty($httpRet)){
            returnJson(6000);
        }

        $httpRet = json_decode($httpRet, true);
        if(empty($httpRet) || $httpRet['code'] != 2000){
            returnJson(6000, '', $httpRet);
        }
        returnJson($httpRet);
    }

    /*
     *创建U9订单
     */
    public function createU9Order(){
        $ids = input('param.ids');
        $idArr = explode('|',$ids);
        $reInfo = [];
        $poLogic = model('Po', 'logic');
        $num = 0;
        foreach($idArr as $k => $v){
            $where = ['id'=>$v];
            $status = $poLogic->getPoStatus($where);
            if($status == 'contract_pass'){//合同审核通过了
                $res = $this->placeOrderAll($v);
                $reInfo[$v] = $res;
                if($res['code'] == 2000){
                    $num += 1;
                }
            }
        }
        return json(['code' => 2000, 'msg' => '成功下了'.$num.'单', 'data' => $reInfo]);
    }

    /*
     * 内部创建U9订单
     */
    public function placeOrderAll($id = ''){
        if($id == ''){
            $id = input('id');
        }
        //echo $id;die;
        $poLogic = model('Po', 'logic');
        $sendData = [];
        $poInfo = $poLogic->getPoInfo($id);

        $sendData['DocDate'] = $poInfo['doc_date'] == '' ? time() : $poInfo['doc_date'];//单价日期
        $sendData['DocTypeCode'] = $poInfo['doc_type'];//单据类型
        $sendData['TCCode'] = $poInfo['tc_code'];//币种编码
        $sendData['bizType'] = $poInfo['biz_type'];//U9参数
        $sendData['isPriceIncludeTax'] = $poInfo['is_include_tax'];//是否含税
        $sendData['supplierCode'] = $poInfo['sup_code'];//供应商代码
        $poItemInfo = $poLogic->getPoItemInfo($poInfo['id']);
        //dump($poItemInfo);die;
        $lines = [];
        foreach($poItemInfo as $k => $v){
            $lines[] = [
                'ItemCode' => $v['item_code'],//料品号
                'OrderPriceTC' => $v['price'],//采购单价
                'OrderTotalTC' => $v['price']*$v['price_num'],//采购总金额
                'ReqQty' => $v['price_num'],//采购数量
                'RequireDate' => $v['req_date'],//请购时间
                'SupConfirmDate' => $v['sup_confirm_date'],//供应商供货日期
                'TaxRate' => $v['tax_rate']*100,//税率
                'TradeUOM' => $v['tc_uom'],//交易单位
                'ValuationQty' => $v['tc_num'],//
                'ValuationUnit' => $v['price_uom'],//
                'srcDocPRLineNo' => $v['pr_ln'],
                'srcDocPRNo' => $v['pr_code']
            ];
        }
        $sendData['lines'] = $lines;
        //exit(json_encode($sendData));
        $httpRet = HttpService::curl(getenv('APP_API_U9').'index/po', $sendData);
        $res = json_decode($httpRet, true);//成功回写数据库
        if($res['code'] != 2000){
            returnjson(6000,'调用U9接口异常',$res);
        }
        $where = [
            'id' => $id,
        ];
        $data = [
            'order_code' => $res['result']['DocNo'],
            'status' => 'init',
        ];
        $res = $poLogic->saveStatus($where, $data);//订单写入数据库
        return ['code'=>2000,'msg'=>'','data'=>$data];
    }

    /*
     * 合并生成订单
     */
    public function placeOrderByPoItem(){
        $ids = input('param.ids');
        $idArr = explode('|',$ids);
        $poLogic = model('Po', 'logic');
        $supLogic = model('Supporter','logic');
        $supCodeInfo = [];
        if(!empty($ids)){
            $poArr = [];
            foreach($idArr as $k=>$v){
                $po_id = $poLogic->getPoId(['id'=>$v]);//判断id是否存在po_id有存在返回不能合并订单操作
                if($po_id){
                    $poArr[$k] = $po_id;
                }
                $supInfo = $poLogic->getSupInfo(['id'=>$v]);//通过id获取sup_code sup_name
                $supCodeInfo[$supInfo['sup_code']] = $supInfo['sup_name'];
            }
        }

        if(!empty($poArr)){
            return json(['code'=>4000,'msg'=>'抱歉，您选择的采购订单中已经存在下单后的订单状态','data'=>[]]);
        }
        if(count($supCodeInfo) != 1){
            return json(['code'=>4000,'msg'=>'抱歉，您选择的采购订单中包含多家供应商或采购订单中供应商已不存在','data'=>[]]);
        }
        foreach($supCodeInfo as $k=>$v){
            $sup_code = $k;
            $sup_name = $v;
        }
        $now = time();
        //进行生成订单
        $itemInfo = $poLogic->getPoItem($idArr[0]);
        //dump($itemInfo);die;
        $poData = [
            'pr_code' => $itemInfo['pr_code'],
            'sup_code' => $sup_code,
            'is_include_tax' => 1,      //是否含税
            'status' => 'init',
            'create_at' => $now,
            'update_at' => $now,
        ];
        $po_id = $poLogic->insertOrGetId($poData);
        //生成关联关系
        $list = [];
        foreach($idArr as $k=>$v){
            $list[$k] = ['id'=>$v,'po_id'=>$po_id,'status'=>'placeorder'];
        }
        $res = $poLogic->updateAllPoid($list);
        $data = [];
        foreach($idArr as $k=>$v){
            $data[$k] = ['id'=>$v,'po_id'=>$po_id,'create_at'=>date('Y-m-d',$now)];
        }
        $res = $this->placeOrderAll($po_id);//内部生成订单
        //dump($res);
        if($res['code'] == 2000){
            //发消息通过$sup_code $sup_name得到$sup_id
            $sup_id = $supLogic->getSupIdVal(['code'=> $sup_code,'name'=> $sup_name]);
            sendMsg($sup_id,'安特威订单','您有新的订单，请注意查收。');//发送消息
            return json(['code'=>2000,'msg'=>'下订单成功','data'=>$data]);
        }
        return json(['code'=>6000,'msg'=>'下订单失败','data'=>$data]);
    }
}