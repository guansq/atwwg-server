<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 14:35
 */

namespace app\spl\controller;

use PHPExcel;
use PHPExcel_IOFactory;
use service\HttpService;
use think\Request;

class Order extends Base{
    protected $title = '采购订单';

    public function getPoList(){
        $orderLogic = model('Order', 'logic');
        $sup_code = session('spl_user')['sup_code'];
        $where = [
            'status' => ['NOT IN',['sup_cancel','atw_cancel']],  //已经取消的不显示在 供应商端
            'sup_code' => $sup_code
        ];
        $data = input('param.');
        $tag = input('tag');
        //        var_dump( $data);
        // 应用搜索条件
        if(!empty($data)){
            foreach(['status'] as $key){
                if(isset($data[$key]) && $data[$key] !== ''){
                    if($key == 'status' && $data[$key] == 'all'){
                        continue;
                    }
                    if($key == 'status' && $data['status'] == 'zr_close'){
                        $where['status'] = 'finish';
                        $where['u9_status'] = 3;
                        continue;
                    }
                    if($key == 'status' && $data['status'] == 'dq_close'){
                        $where['status'] = 'finish';
                        $where['u9_status'] = 4;
                        continue;
                    }
                    if($key == 'status' && $data['status'] == 'ce_close'){
                        $where['status'] = 'finish';
                        $where['u9_status'] = 5;
                        continue;
                    }
                    $where[$key] = $data[$key];
                }
            }
            if(!empty($data['contract_begintime']) && !empty($data['contract_endtime'])){
                $where['contract_time'] = array(
                    'between',
                    array(strtotime($data['contract_begintime']), strtotime($data['contract_endtime']))
                );
            }elseif(!empty($data['contract_begintime'])){
                $where['contract_time'] = array('egt', strtotime($data['contract_begintime']));
            }elseif(!empty($data['contract_endtime'])){
                $where['contract_time'] = array('elt', strtotime($data['contract_endtime']));
            }
        }
        $list = $orderLogic->getPolist($where, $tag);

        $returnInfo = [];
        $status = [
            '' => '',
            'init' => '待确认',
            'sup_cancel' => '已取消',
            'sup_sure' => '待上传',
            'sup_edit' => '修改交期',
            'atw_cancel'=>'已取消 ',
            'atw_sure'=>'已确定',
            'upload_contract' => '待审核',
            'contract_pass' => '审核通过',
            'contract_refuse' => '审核拒绝',
            'executing' => '执行中',
            'finish' => '关闭',
            'zr_close' => '自然关闭',
            'dq_close' => '短缺关闭',
            'ce_close' => '超额关闭',
        ];
        // var_dump($list);
        foreach($list as $k => $v){
            $returnInfo[$k]['checked'] = $v['id'];
            $exec_desc = '';
            if(!empty($itemInfo = $orderLogic->getPoItemInfo($v['id']))){
                foreach($itemInfo as $vv){
                    $vv['arv_goods_num'] = $vv['arv_goods_num'] == '' ? 0 : $vv['arv_goods_num'];
                    $vv['pro_goods_num'] = $vv['pro_goods_num'] == '' ? 0 : $vv['pro_goods_num'];
                    $exec_desc .= '物料名称：'.$vv['item_name'].'; '.'已送货数量：'.$vv['arv_goods_num'].'; 未送货数量：'.$vv['pro_goods_num'].'<br>';
                }
                $returnInfo[$k]['exec_desc'] = $exec_desc;
            }else{
                $returnInfo[$k]['exec_desc'] = '';
            }
            $returnInfo[$k]['order_code'] = $v['order_code'];
            //$returnInfo[$k]['pr_code'] = $v['pr_code'];
            //  $returnInfo[$k]['pr_date'] = date('Y-m-d',$offerLogic->getPrDate($v['pr_code']));
            if($v['status'] == 'finish'){
                if($v['u9_status'] == 3){
                    $v['status'] ='zr_close';
                }elseif($v['u9_status'] == 4){
                    $v['status'] ='dq_close';
                }elseif($v['u9_status'] == 5){
                    $v['status'] ='ce_close';
                }
            }
            $returnInfo[$k]['create_at'] = date('Y-m-d', $v['create_at']);
            $returnInfo[$k]['status'] = $status[$v['status']];
            $returnInfo[$k]['contract_time'] = empty($v['contract_time']) ? '--' : date('Y-m-d', $v['contract_time']);
            $returnInfo[$k]['id'] = $v['id'];
        }
        return $returnInfo;
    }


    public function getOrderList(){
        $returnInfo = $this->getPoList();
        $info = ['draw' => time(), 'data' => $returnInfo, 'extData' => [],];
        return json($info);
    }

    public function index(){
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
        $this->assign('title', $this->title);
        return view();
    }

    public function detail(){
        $po_id = input('id');
        //$pr_code = '1111222';
        $offerLogic = model('Order', 'logic');
        $piList = $offerLogic->getOrderDetailInfo($po_id);
        $orderamount = 0;
        foreach($piList as $key => &$item){
            $orderamount += $item['amount'];
            $result = $offerLogic->getOrderRecordInfo($item['id']);
            $piList[$key]['times'] = (!empty($result) ? count($result) : 0);
            $item['sup_update_date_str'] = empty($item['sup_update_date']) ? '' : date('Y-m-d', $item['sup_update_date']);
        }
        $codeInfo = $offerLogic->getOrderListOneInfo($po_id);
        $contractable = in_array($codeInfo[0]['status'], array(
            'sup_sure',
            'upload_contract',
            'contract_refuse'
        )) ? '1' : '0';
        $cancelable = in_array($codeInfo[0]['status'], array('init', 'atw_sure')) ? '1' : '0';
        $confirmorderable = in_array($codeInfo[0]['status'], array('init', 'atw_sure')) ? '1' : '0';
        $confirmable = !in_array($codeInfo[0]['status'], ['sup_cancel', 'finish']) ? '1' : '0';
        $printRcvAble = !in_array($codeInfo[0]['status'], ['sup_cancel', 'finish']) ? '1' : '0';
        $statusButton = array(
            'contractable' => $contractable,
            'cancelable' => $cancelable,
            'confirmable' => $confirmable,
            'confirmorderable' => $confirmorderable,
            'printRcvAble' => $printRcvAble
        );
        $imgInfos = explode('|', $codeInfo[0]['contract']);
        $imgInfos = array_filter($imgInfos);
        $this->assign('statusButton', $statusButton);
        $this->assign('imgInfos', $imgInfos);
        // var_dump($detail);
        $this->assign('list', $piList);
        $this->assign('orderamount', $orderamount);
        if(empty($codeInfo[0]['order_code'])){
            $codeInfo[0]['order_code'] = '--';
        }
        if(empty($codeInfo[0]['contract_time'])){
            $codeInfo[0]['contract_time'] = '--';
        }else{
            $codeInfo[0]['contract_time'] = atwDate($codeInfo[0]['contract_time']);
        }


        $statusArr = [
            '' => '',
            'init' => '待确认',
            'sup_cancel' => '已取消',
            'sup_sure' => '待上传',
            'sup_edit' => '修改交期',
            'atw_cancel'=>'已取消 ',
            'atw_sure'=>'已确定',
            'upload_contract' => '待审核',
            'contract_pass' => '审核通过',
            'contract_refuse' => '审核拒绝',
            'executing' => '执行中',
            'finish' => '关闭',
        ];
        $u9statusArr = [
            '' => '',
            '3' => '自然关闭',
            '4' => '短缺关闭',
            '5' => '超额关闭',
        ];

        $statusStr = $statusArr[$codeInfo[0]['status']];
        if($codeInfo[0]['u9_status']){
            $statusStr = $u9statusArr[$codeInfo[0]['u9_status']];
        }
        $codeInfo[0]['statusStr'] = $statusStr;
        $this->assign('codeInfo', $codeInfo[0]);
        return view();
    }

    public function cancel(){
        $id = input('id');
        $poLogic = model('Order', 'logic');
        $supLogic = model('SupplierInfo', 'logic');
        $po = $poLogic->find($id);
        if(empty($po)){
            returnJson(4004);
        }
        //更改po.status
        $po->status = 'sup_cancel';
        $po->save();  //FIXME
        // 通知到责任采购
        $supCode = session('spl_user')['sup_code'];
        $supInfo = $supLogic->findByCode($supCode);
        $msg = "供应商[$supInfo[code] $supInfo[name]]取消了采购订单[$po[order_code]]。供应商联系方式：$supInfo[ctc_name] $supInfo[mobile] $supInfo[phone] $supInfo[email]。 \n -- 物供平台 ".date('Y-m-d H:i');
        if(!empty($supInfo['purch_email'])){
            $sendData = [
                'rt_appkey' => getenv('APP_RT_APP_KEY'),
                'fromName' => '安特威物供平台',//发送人名
                'to' => $supInfo['purch_email'],
                'subject' => '供应商取消采购订单',
                'html' => $msg.' 本邮件由安特威物供平台系统发送，请勿回复。',
                'from' => 'atwwg@antiwearvalve.com',//平台的邮件头
            ];
            HttpService::curl(getenv('APP_API_MSG').'SendEmail/sendHtml', $sendData);
        }
        if(!empty($supInfo['purch_mobile'])){
            $sendData = [
                'mobile' => $supInfo['purch_mobile'],
                'rt_appkey' => getenv('APP_RT_APP_KEY'),
                'text' => $msg,
            ];
            HttpService::curl(getenv('APP_API_MSG').'SendSms/sendText', $sendData);//sendSms($data)
        }
        return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
    }

    public function orderconfirm(){
        $id = input('id');
        $offerLogic = model('Order', 'logic');
        $detail = $offerLogic->updateStatus($id, 'sup_sure');
        if($detail){
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 打印送货单
     * @param Request $request
     * @return \think\response\View
     */
    public function printRcv(Request $request){
        $poLogic = model('Order', 'logic');
        $reqParmas = $this->getReqParams();
        if($request->isGet()){
            $piList = $poLogic->getOrderDetailInfo($reqParmas['id']);
            $this->assign('piList', $piList);
            $this->assign('id', $reqParmas['id']);
            return view();
        }elseif($request->isPost()){
            $rcvLogic = model('PoReceive', 'logic');
            $ret = $rcvLogic->createReturnCode($reqParmas);
            if($ret['code'] != 2000){
                $this->error($ret['msg'], '', $ret['result']);
            }
            $this->success($ret['msg'], '', $ret['result']);
        }

    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 打印条形码 弹框
     * @param Request $request
     * @return \think\response\View
     */
    public function barCodeModal(Request $request){
        $pILogic = model('PoItem', 'logic');
        $reqParmas = $this->getReqParams(['pi_id']);
        $pi = $pILogic->find($reqParmas['pi_id']);
        $this->assign('pi', $pi);
        return view();

    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 打印条形码
     * @param Request $request
     * @return \think\response\View
     */
    public function saveBarCode(Request $request){
        $pILogic = model('PoItem', 'logic');
        $bcLogic = model('BarCode', 'logic');
        $itemLogic = model('Item', 'logic');
        $reqParams = $this->getReqParams(['pi_id', 'num', 'facture_date', 'heat_no', 'remark']);
        $pi = $pILogic->find($reqParams['pi_id']);
        if(empty($pi)){
            returnJson(4004);
        }
        $item = $itemLogic->findByCode($pi['item_code']);
        if(empty($item)){
            returnJson(4004);
        }

        $printParmas = [
            'LotNo' => '',                        //todo 物料条码
            'ItemCode' => $pi['item_code'],       //物料编码
            'ItemName' => $pi['item_name'],       //物料名称
            'ItemStd' => $item['specs'],                      //物料规格
            'MaterialTexture' => $item['mat_quality'],          //材质
            'Quantity' => $reqParams['num'],         //数量
            'MeasurementUnit' => $pi['price_uom'],          //计量单位
            'ManufactureDate' => strtotime($reqParams['facture_date']),            //生产日期
            'HeatNumber' => $reqParams['heat_no'],           //炉号
            'VendorName' => $pi['sup_name'],           //供应商名称
            'Remark' => $reqParams['remark'],           //备注
        ];

        returnJson($bcLogic->saveBarCode($printParmas));
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 打印条形码
     * @param Request $request
     * @return \think\response\View
     */
    public function printBarCode(Request $request){
        $bcLogic = model('BarCode', 'logic');
        $reqParmas = $this->getReqParams(['lot_no']);
        $barCode = $bcLogic->findByCode($reqParmas['lot_no']);
        if(empty($barCode)){
            exit('<script>window.close();</script>');
        }
        return $bcLogic->printBarCode($barCode);
    }


    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 下载送货单
     */
    public function downDeliverOrder(){
        $rcvCode = input('rcvCode');
        $poRcvLogic = model('PoReceive', 'logic');
        return $poRcvLogic->downPoReceive($rcvCode);
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 供应商修改交期
     * @return \think\response\Json
     */
    public function updateSupconfirmdate(){
        $id = input('id');
        $supconfirmdate = strtotime(input('supconfirmdate'));
        $orderLogic = model('Order', 'logic');
        $poRecLogic = model('PoRecord', 'logic');
        $pi = model('PoItem', 'logic')->find($id);
        //var_dump($detailInfo);
        if(empty($pi)){
            return json(['code' => 4004, 'msg' => '获取消息失败', 'data' => []]);
        }
        $times = $poRecLogic->countByPiId($id);
        if($times > 3){
            returnJson(4010, '修改次数已经超过三次');
        }

        $u9Ret = $orderLogic->updateU9Supconfirmdate($pi, $supconfirmdate);
        if($u9Ret['code'] != 2000){
            return returnJson($u9Ret);
        }

        //        if(empty($u9Ret['result']['IsSuccess'])){
        //            return returnJson(6000);
        //        }

        $data = [
            'pi_id' => $id,
            'create_at' => time(),
            'update_at' => time(),
            'promise_date' => $pi['sup_confirm_date'],
            'seq' => $times + 1
        ];
        if(!$poRecLogic->data($data)->save()){
            return json(['code' => 5000, 'msg' => '保存po_record 失败', 'data' => []]);
        }
        //记录修改次数
        $sup_code = session('spl_user')['sup_code'];
        model('SupplierInfo', 'logic')->where('code', $sup_code)->setInc('readjust_count');
        $detail = $orderLogic->updateSupconfirmdate($id, $supconfirmdate);
        //$detailPo = $orderLogic->updateStatus($pi['po_id'], 'sup_edit');
        if($detail){
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 5000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 供应商 clean 合同影像
     * @return \think\response\Json
     */
    public function cleanContractImg(){
        $id = input('id');
        $orderLogic = model('Order', 'logic');
        $dbRet = $orderLogic->where('id', $id)->update([
            'contract' => '',
            'contract_time' => null,
            'status' => 'sup_sure'
        ]);
        if($dbRet){
            returnJson(2000);
        }
        returnJson(5000);
    }


    public function add(){
        if(request()->isPost()){
            $data = input('param.');
            $id = $data['id'];
            $contract = input('contract');
            $src = empty($contract) ? $data['src'] : $contract.'|'.$data['src'];
            $offerLogic = model('Order', 'logic');
            $result = $offerLogic->updatecontract($id, $src, 'upload_contract');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            $id = input('id');
            $contract = input('contract');
            $this->assign('id', $id);
            $this->assign('contract', $contract);
            return view();
        }

    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 下载合同模版
     */
    public function downContract(){
        $id = input('id');
        $poLogic = model('Order', 'logic');
        $sup_code = session('spl_user')['sup_code'];
        $po = $poLogic->where('sup_code', $sup_code)->where('id', $id)->find();
        if(empty($po)){
            $this->error('无效的id='.$id, '');
        }

        return $poLogic->downContract($po);
    }


    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe:导出表格  discard
     */
    function exportPoExcel(){
        $list = $this->getPoList();
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        //dump($list);die;请购单编号-物料编号-请购日期-评标日期-供应商名称-要求交期-承诺交期-采购数量-报价-小计-状态
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('采购订单列表'); //给当前活动sheet设置名称

        $PHPSheet->setCellValueExplicit('A1', '订单编号');
        $PHPSheet->setCellValueExplicit('B1', '物料交付情况');
        $PHPSheet->setCellValueExplicit('C1', '状态');
        $PHPSheet->setCellValueExplicit('D1', '合同签订日期');
        $num = 1;
        foreach($list as $k => $v){
            $v['exec_desc'] = str_replace('<br>', "\r\n", $v['exec_desc']);
            $num = $num + 1;
            $PHPSheet->setCellValueExplicit('A'.$num, $v['order_code'])
                ->setCellValueExplicit('B'.$num, $v['exec_desc'])
                ->setCellValueExplicit('C'.$num, $v['status'])
                ->setCellValueExplicit('D'.$num, $v['contract_time']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/poItemList.xlsx'); //表示在$path路径下面生成ioList.xlsx文件
        $file_name = "安特威采购订单".date('Y-m-d', time()).".xlsx";
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
        $piLogic = model('PoItem', 'logic');
        $reqParams = $this->getReqParams(['status'=>'','contract_begintime'=>'','contract_endtime'=>'','sup'=>'']);
        $reqParams['sup']= session('spl_user')['sup_code'];
        $piPage = $piLogic->getPoItemPage($reqParams,1,PHP_INT_MAX);
        $list = $piPage->getItemList();

        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        //dump($list);die;请购单编号-物料编号-请购日期-评标日期-供应商名称-要求交期-承诺交期-采购数量-报价-小计-状态
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        foreach(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'] as $cl ){
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
        $PHPSheet->setCellValueExplicit('L1', '小计');
        $PHPSheet->setCellValueExplicit('M1', '到货数量');
        $PHPSheet->setCellValueExplicit('N1', '未交数量');
        $PHPSheet->setCellValueExplicit('O1', '退货数量');
        $PHPSheet->setCellValueExplicit('P1', '采购员');
        $PHPSheet->setCellValueExplicit('Q1', '项目号');
        $PHPSheet->setCellValueExplicit('R1', '状态');
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
                ->setCellValue('J'.$num, $v['price_num'])
                ->setCellValue('K'.$num,$v['price'])
                ->setCellValue('L'.$num, $v['price']*$v['price_num'])
                ->setCellValue('M'.$num, $v['arv_goods_num'])
                ->setCellValue('N'.$num, $v['pro_goods_num'])
                ->setCellValue('O'.$num, $v['return_goods_num'])
                ->setCellValueExplicit('P'.$num, $v['purch_name'])
                ->setCellValueExplicit('Q'.$num, $v['pro_no'])
                ->setCellValueExplicit('R'.$num, empty($v['u9_status'])?'执行中':$v['u9_status']);
            $PHPSheet ->getStyle('J'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $PHPSheet ->getStyle('K'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $PHPSheet ->getStyle('L'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $PHPSheet ->getStyle('M'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $PHPSheet ->getStyle('N'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $PHPSheet ->getStyle('O'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

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

}