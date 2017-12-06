<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */

namespace app\admin\controller;

use app\common\model\Po as PoModel;
use PHPExcel;
use PHPExcel_IOFactory;
use service\HttpService;

class Requireorder extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '请购单管理';
    const PURATTR = [
        'tech' => '技术型',
        'compete' => '竞争型',
        'single' => '单一资源型'
    ];

    public function index(){
        //echo '111111111';die;
        $this->assign('title', $this->title);
        $logicPrInfo = Model('RequireOrder', 'logic');
        $allNums = $logicPrInfo->getListNum();
        $this->assign('allNums', $allNums);
        //echo $allNums;
        return view();
    }

    public function getPrList(){
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $whereInfo = [
            'pr_code',
            'pr_date',
            'item_code',
            'item_name',
            'pro_no',
            'pur_attr',
            'status',
            'is_appoint_sup',
            'check_status'
        ];//继续筛选操作
        $reqParams = $this->getReqParams($whereInfo);
        $isNeedAppointSup = input('is_need_appoint_sup');

        if(isset($reqParams['pr_date']) && $reqParams['pr_date'] !== ''){
            $reqParams['pr_date'] = strtotime($reqParams['pr_date']);
        }
        $where = [];
        // 应用搜索条件
        foreach($whereInfo as $key){
            if(isset($reqParams[$key]) && $reqParams[$key] !== ''){
                $where[$key] = ['like', "%{$reqParams[$key]}%"];
            }
        }

        $logicPrInfo = Model('RequireOrder', 'logic');
        $list = $logicPrInfo->getPrList($start, $length, $where, $isNeedAppointSup);
        $returnArr = [];
        //dump($list);die;
        //init=初始,hang=挂起,inquiry=询价中,quoted = 供应商全部报价完毕,flow = 流标,winbid=中标,order=已下单,close = 关闭
        $status = [
            'init' => '待询价',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'quoted' => '待评标',
            'flow' => '流标',
            'winbid' => '中标',
            'wait' => '待下单',
            'order' => '自然关闭', //文案展示客户要求
            'close' => '短缺关闭', //客户为了区分其他状态 ， 目前关闭的PR只有短缺关闭状态
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
            'compete' => '充分竞争',
            'have_price' => '有价格',
            'no_sup' => '无匹配供应商'
        ];
        //dump($list);die;
        foreach($list as $k => $v){
            //status为init状态可以指定
            $is_appoint_sup = $v['is_appoint_sup'];//先获取是否指定
            if($v['status'] == 'init' || $v['status'] == 'hang'){//订单挂起状态 且订单为初始状态
                if($v['status'] == 'init'){

                    //$v['check_status'] = '';
                    if($v['is_appoint_sup'] == 1){
                        if(!empty($v['appoint_sup_name'])){
                            $inquiry = $v['appoint_sup_name'];
                            if(key_exists($v['check_status'], $checkStatus)){

                                //$v['check_status'] = $v['check_status'];
                                if($v['check_status'] == ''){
                                    $v['check_status'] = '<a href="javascript:;" onclick="checkStatus(\'agree\','.$v['id'].');">通过</a>
                                &nbsp;&nbsp;<a href="javascript:;" onclick="checkStatus(\'refuse\','.$v['id'].');">拒绝</a>';
                                }else{
                                    $v['check_status'] = $checkStatus[$v['check_status']];
                                }
                                //$v['check_status'] = 'test';
                            }else{
                                $v['check_status'] = $v['check_status'];
                                //$v['check_status'] = 'test';
                            }
                        }else{
                            //选择供应商
                            $inquiry = '<a class="select_sell" href="javascript:void(0);" onclick="bomb_box(event,\''.$v['pr_code'].'\',\''.$v['item_code'].'\',\''.$v['id'].'\',\''.$v['item_name'].'\');" data-url="'.url('requireorder/selectSup', array(
                                    'pr_code' => $v['pr_code'],
                                    'item_code' => $v['item_code']
                                )).'">选择供应商</a>';
                            $v['check_status'] = $checkStatus[$v['check_status']];
                        }
                    }else{
                        $inquiry = $inquiry_way[$v['inquiry_way']];
                        //$inquiry = 'test';
                    }
                }else{
                    if($is_appoint_sup == 1){//为指定状态 --->挂起状态下的指定
                        if(!empty($v['appoint_sup_code'])){//指定状态下有供应商的名称
                            $inquiry = $v['appoint_sup_name'];
                            //$inquiry = 'test';
                            if($v['check_status'] == ''){//判断主管审核
                                //auth();
                                $v['check_status'] = '<a href="javascript:;" onclick="checkStatus(\'agree\','.$v['id'].');">通过</a>
                                &nbsp;&nbsp;<a href="javascript:;" onclick="checkStatus(\'refuse\','.$v['id'].');">拒绝</a>';
                            }else{
                                if(key_exists($v['check_status'], $checkStatus)){
                                    $v['check_status'] = $checkStatus[$v['check_status']];
                                }else{
                                    $v['check_status'] = $v['check_status'];
                                }
                            }
                        }else{//非指定状态下无供应商名称---->选择供应商 && 无审核状态
                            $v['check_status'] = '';
                            $inquiry = '<a class="select_sell" href="javascript:void(0);" 
                            onclick="bomb_box(event,\''.$v['pr_code'].'\',\''.$v['item_code'].'\',\''.$v['id'].'\',\''.$v['item_name'].'\');" data-url="'.url('requireorder/selectSup', array(
                                    'pr_code' => $v['pr_code'],
                                    'item_code' => $v['item_code']
                                )).'">选择供应商</a>';
                        }

                    }else{//挂起 &&  非指定状态
                        $inquiry = $inquiry_way[$v['inquiry_way']];
                        $v['check_status'] = '';
                    }
                }
                //init 和 hang 只要 appoint_sup_code 为可以选择供应商都可以指定
                if($is_appoint_sup == 1){
                    $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_id="'.$v['id'].'" data-pr_code="'.$v['pr_code'].'" data-item_code="'.$v['item_code'].'" checked class="ver_top" checked value="1">指定';//有指定的时候
                    $v['is_appoint_sup'] .= '<p id="cancel'.$v['id'].'"></p>';
                }else{
                    $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_id="'.$v['id'].'" data-pr_code="'.$v['pr_code'].'" data-item_code="'.$v['item_code'].'" class="ver_top" value="0">指定';//没有指定的时候
                    $v['is_appoint_sup'] .= '<p id="cancel'.$v['id'].'"><a href="javascript:cancelPoint('.$v['id'].');">取消指定</a></p>';
                }
            }else{//订单非挂起 和  非指定
                $v['is_appoint_sup'] = '';
                $v['check_status'] = '';
                if(key_exists($v['inquiry_way'], $inquiry_way)){
                    $inquiry = $inquiry_way[$v['inquiry_way']];
                }else{
                    //$v['inquiry_way'] = $inquiry_way[$v['inquiry_way']];
                    //$v['inquiry_way'] = '其他';
                    $inquiry = $v['inquiry_way'];
                }
            }

            // 流标的 且 有供应商的 可以重新指定供应商下单
            if($v['status'] == 'flow' && $v['inquiry_way'] != 'no_sup'){
                $inquiry = $inquiry_way[$v['inquiry_way']];
                $inquiry .= '<br/> <a class="select_sell" href="javascript:void(0);" onclick="bomb_box(event,\''.$v['pr_code'].'\',\''.$v['item_code'].'\',\''.$v['id'].'\',\''.$v['item_name'].'\');" data-url="'.url('requireorder/selectSup', array(
                        'pr_code' => $v['pr_code'],
                        'item_code' => $v['item_code']
                    )).'">选择供应商</a>';


            }
            $returnArr[] = [
                'id' => $v['id'],
                //请购单id
                'pr_code' => $v['pr_code'],
                //请购单号
                'pr_date' => date('Y-m-d', $v['pr_date']),
                //请购日期
                'item_code' => $v['item_code'],
                //料号
                'desc' => $v['item_name'],
                //物料描述
                'pro_no' => $v['pro_no'],
                //项目号
                'tc_uom' => $v['tc_uom'],
                //交易单位
                'tc_num' => $v['tc_num'],
                //交易数量
                'price_uom' => $v['price_uom'],
                //计价单位
                'price_num' => $v['price_num'],
                //计价数量
                'req_date' => date('Y-m-d', $v['req_date']),
                //交期
                'status' => key_exists($v['status'], $status) ? $status[$v['status']] : $v['status'],
                //状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => key_exists($v['pur_attr'], self::PURATTR) ? self::PURATTR[$v['pur_attr']] : $v['pur_attr'],
                //物料采购属性
                'is_appoint_sup' => $v['is_appoint_sup'],
                //是否指定供应商
                'inquiry_way' => $inquiry,
                //询价方式
                'check_status' => $v['check_status'],
                //主管审批
            ];

        }

        $info = [
            'draw' => time(),
            'recordsTotal' => $logicPrInfo->getListNum($where, $isNeedAppointSup),
            'recordsFiltered' => $logicPrInfo->getListNum($where, $isNeedAppointSup),
            'data' => $returnArr
        ];

        return json($info);
    }

    /*
     * 选择物料所对应的多个供应商
     */
    public function showSelectSup(){
        $data = input('param.');
        $result = $this->validate($data, 'Enquiry');
        if($result !== true){
            return json(['code' => 4000, 'msg' => $result, 'data' => []]);
        }
        $logicPrInfo = Model('RequireOrder', 'logic');
        $list = $logicPrInfo->getSupList($data['item_code']);
        foreach($list as $k => $v){
            $list[$k]['pr_code'] = $data['pr_code'];
        }
        return json($list);
    }

    /*
     * 更改询价状态
     */
    public function changeInquiType(){
        $data = input('param.');
        $logicPrInfo = Model('RequireOrder', 'logic');

        if($data['is_appoint_sup'] == 0){
            $dataArr = [
                'status' => 'init',
                'inquiry_way' => '',
                'is_appoint_sup' => 0,
                'appoint_sup_code' => '',
                'appoint_sup_name' => '',
                'check_status' => '',
            ];

        }else if($data['is_appoint_sup'] == 1){
            $dataArr = [
                'status' => 'hang',
                'inquiry_way' => 'assign',
                'is_appoint_sup' => 1,
            ];
        }else{
            return json(['code' => 4000, 'msg' => '请传入合法的is_appoint_sup参数值', 'data' => []]);
        }
        $where = [
            'pr_code' => $data['pr_code'],
            'item_code' => $data['item_code'],
        ];
        $result = $logicPrInfo->updateByPrCode($where, $dataArr);
        if(false === $result){
            return json(['code' => 4000, 'msg' => '更改指定供应商状态失败', 'data' => []]);
        }
        return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
    }

    /*
     * 保存到pr表
     */
    public function savePrToOrder(){
        $data = input('param.');
        $logicPrInfo = Model('RequireOrder', 'logic');
        $dataArr = [
            'is_appoint_sup' => $data['is_appoint_sup'],
            'appoint_sup_code' => $data['appoint_sup_code'],
            'appoint_sup_name' => $data['appoint_sup_name'],
        ];
        $where = [
            'pr_code' => $data['pr_code'],
            'item_code' => $data['item_code'],
        ];
        $data['point_date'] = strtotime($data['point_date']);
        $item_id = $data['item_id'];
        $sup_code = $data['appoint_sup_code'];
        $sup_name = $data['appoint_sup_name'];
        //得到pr_info
        $prInfo = $logicPrInfo->getPrInfo(['id' => $data['item_id']]);
        $sendInfo = [];
        if($prInfo){
            $sendInfo['item_code'] = $prInfo['item_code'];
            $sendInfo['price'] = $data['point_price'];
            $sendInfo['price_num'] = $prInfo['price_num'];
            $sendInfo['req_date'] = $prInfo['req_date'];//需求日期
            $sendInfo['sup_confirm_date'] = $data['point_date'];
            $sendInfo['tax_rate'] = $prInfo['tax_rate'];
            $sendInfo['tc_uom'] = $prInfo['tc_uom'];
            $sendInfo['tc_num'] = $prInfo['tc_num'];
            $sendInfo['price_uom'] = $prInfo['price_uom'];
            $sendInfo['price_uom_code'] = $prInfo['price_uom_code'];
            $sendInfo['pr_ln'] = $prInfo['pr_ln'];
            $sendInfo['pr_code'] = $prInfo['pr_code'];
            $sendInfo['sup_code'] = $data['appoint_sup_code'];
            $sendInfo['pro_no'] = $prInfo['pro_no'];
        }
        $ret = placeOrder($sendInfo);//生成PO表 生成PO ITEM表

        if($ret['code'] != 2000){
            $msg = $ret['msg'];
            if(!empty($ret['data']['Message'])){
                $msg .= ':'.$ret['data']['Message'];
            }
            return json(['code' => $ret['code'], 'msg' => $msg, 'data' => []]);
        }

        $result = $logicPrInfo->updateByPrCode($where, $dataArr);
        if(false === $result){
            return json(['code' => 4000, 'msg' => '指定供应商状态失败', 'data' => []]);
        }

        $docNo = $ret['data']['DocNo'];//生成PO表 生成PO ITEM表
        if($docNo){
            $poLogic = model('Po', 'logic');
            $now = time();
            //生成一条po记录
            $poData = [
                //'pr_code' => $itemInfo['pr_code'],
                'order_code' => $docNo,
                'sup_code' => $data['appoint_sup_code'],
                'doc_date' => $now,
                'is_include_tax' => 1,      //是否含税
                'status' => 'init',
                'create_at' => $now,
                'update_at' => $now,
            ];
            PoModel::deletePoPi($docNo);
            $po_id = $poLogic->insertOrGetId($poData);
            //生成poItem
            $poItemData = [
                'po_id' => $po_id,
                'po_code' => $docNo,
                'item_code' => $prInfo['item_code'],
                'item_name' => $prInfo['item_name'],
                'sup_code' => $data['appoint_sup_code'],
                'sup_name' => $data['appoint_sup_name'],
                'price_num' => $prInfo['price_num'],
                'price_uom' => $prInfo['price_uom'],
                'tc_num' => $prInfo['tc_num'],
                'tc_uom' => $prInfo['tc_uom'],
                'pr_code' => $prInfo['pr_code'],
                'pr_id' => $prInfo['id'],
                'pr_ln' => $prInfo['pr_ln'],
                'pro_no' => $prInfo['pro_no'],
                'sup_confirm_date' => $data['point_date'],
                'req_date' => $prInfo['req_date'],
                'price' => $data['point_price'],
                'tax_price' => $data['point_price'] + ($prInfo['tax_rate']*$data['point_price']),//
                'amount' => $data['point_price']*$prInfo['tc_num'],
                'pro_goods_num' => $prInfo['tc_num'],
                'tc_uom_code' => $prInfo['tc_uom_code'],          //交易单位编码
                'price_uom_code' => $prInfo['price_uom_code'],     //计价单位编码
                'tax_rate' => $prInfo['tax_rate'],
                'create_at' => $now,
                'update_at' => $now,
                'status' => 'placeorder',
            ];
            //dd($poItemData);
            if($po_id === false){
                return json(['code' => 6000, 'msg' => '生成订单失败', 'data' => ['sup_name' => $data['appoint_sup_name']]]);
            }
            //if($po_id){}
            $res = $poLogic->savePoItem($poItemData);
            if($res === false){
                return json([
                    'code' => 6000,
                    'msg' => '生成未下单订单失败',
                    'data' => ['sup_name' => $data['appoint_sup_name']]
                ]);
            }
            //if($res){}
            $where = ['id' => $item_id];
            $ret = $logicPrInfo->updatePr($where, ['status' => 'order']);
            if($ret === false){
                return json(['code' => 6000, 'msg' => '更改下单状态失败', 'data' => ['sup_name' => $data['appoint_sup_name']]]);
            }
        }
        return json(['code' => 2000, 'msg' => '成功', 'data' => ['sup_name' => $data['appoint_sup_name']]]);
        //is_appoint_sup 1 //appoint_sup_code //appoint_sup_name
    }


    /*
     * 指定供应商下单
     */
    public function savePr(){
        $data = input('param.');
        $logicPrInfo = Model('RequireOrder', 'logic');
        $data['point_date'] = strtotime($data['point_date']);
        $item_id = $data['item_id'];
        //得到pr_info
        $prInfo = $logicPrInfo->getPrInfo(['id' => $data['item_id']]);
        $poLogic = model('Po', 'logic');
        $now = time();
        //生成poItem
        $poItemData = [
            'po_id' => null,
            'po_code' => null,
            'po_ln' => null,
            'item_code' => $prInfo['item_code'],
            'item_name' => $prInfo['item_name'],
            'sup_code' => $data['appoint_sup_code'],
            'sup_name' => $data['appoint_sup_name'],
            'price_num' => $prInfo['price_num'],
            'price_uom' => $prInfo['price_uom'],
            'tc_num' => $prInfo['tc_num'],
            'tc_uom' => $prInfo['tc_uom'],
            'pr_code' => $prInfo['pr_code'],
            'pr_id' => $prInfo['id'],
            'pr_ln' => $prInfo['pr_ln'],
            'sup_confirm_date' => $data['point_date'],
            'req_date' => $prInfo['req_date'],
            'price' => $data['point_price'],
            'tax_price' => $data['point_price'] + ($prInfo['tax_rate']*$data['point_price']),//
            'amount' => $data['point_price']*$prInfo['tc_num'],
            'tc_uom_code' => $prInfo['tc_uom_code'],          //交易单位编码
            'price_uom_code' => $prInfo['price_uom_code'],     //计价单位编码
            'tax_rate' => $prInfo['tax_rate'],
            'is_spilt' => $prInfo['is_spilt'],
            'is_persent' => $data['is_persent'],
            'status' => 'init',
            'pro_goods_num' => $prInfo['tc_num'],
            'create_at' => $now,
            'update_at' => $now,
        ];
        //dd($poItemData);

        $res = $poLogic->savePoItem($poItemData);
        if($res === false){
            return json(['code' => 6000, 'msg' => '生成未下单订单失败', 'data' => ['sup_name' => $data['appoint_sup_name']]]);
        }

        $where = ['id' => $item_id];
        $dataArr = [
            'is_appoint_sup' => $data['is_appoint_sup'],
            'appoint_sup_code' => $data['appoint_sup_code'],
            'appoint_sup_name' => $data['appoint_sup_name'],
            'status' => 'wait'
        ];
        $ret = $logicPrInfo->updatePr($where, $dataArr);
        if($ret === false){
            return json(['code' => 6000, 'msg' => '更改PR表失败', 'data' => ['sup_name' => $data['appoint_sup_name']]]);
        }

        return json(['code' => 2000, 'msg' => '成功', 'data' => ['sup_name' => $data['appoint_sup_name']]]);
    }


    /*
     * 批量指定供应商下单
     *
     * date:"2017-09-20"
     * prIds:"24548,2322"
     * price:"12"
     * supCode:"PC-014"
     */
    public function batchSavePr(){
        $reqParams = $this->getReqParams(['date', 'prIds', 'price', 'supCode', 'supName', 'is_persent']);
        $rule = [
            'date' => 'require|length:10,11',
            'prIds' => 'require',
            'supCode' => 'require',
            'supName' => 'require',
        ];
        validateData($reqParams, $rule);
        if($reqParams['is_persent'] == 1){
            $reqParams['price'] = 0;
        }elseif(empty( $reqParams['price'])){
            returnJson(4002, "非赠品价格不能为0。");
        }
        $reqParams['point_date'] = strtotime($reqParams['date']);
        $reqParams['point_price'] = floatval($reqParams['price']);
        $now = time();
        $prLogic = Model('RequireOrder', 'logic');
        $poLogic = model('Po', 'logic');

        //得到pr_info
        $prInfo = $prLogic->where("id IN ($reqParams[prIds])")->select();
        if(empty($prInfo)){
            returnJson(4002, "无效的prids=$reqParams[prIds]");
        }

        foreach($prInfo as $pr){
            //生成poItem
            $poItemData = [
                'po_id' => null,
                'po_code' => null,
                'po_ln' => null,
                'item_code' => $pr['item_code'],
                'item_name' => $pr['item_name'],
                'sup_code' => $reqParams['supCode'],
                'sup_name' => $reqParams['supName'],
                'price_num' => $pr['price_num'],
                'price_uom' => $pr['price_uom'],
                'tc_num' => $pr['tc_num'],
                'tc_uom' => $pr['tc_uom'],
                'pr_code' => $pr['pr_code'],
                'pr_id' => $pr['id'],
                'pr_ln' => $pr['pr_ln'],
                'sup_confirm_date' => $reqParams['point_date'],
                'req_date' => $pr['req_date'],
                'price' => $reqParams['point_price'],
                'tax_price' => $reqParams['point_price'] + ($pr['tax_rate']*$reqParams['point_price']),//
                'amount' => $reqParams['point_price']*$pr['tc_num'],
                'tc_uom_code' => $pr['tc_uom_code'],          //交易单位编码
                'price_uom_code' => $pr['price_uom_code'],     //计价单位编码
                'pro_no' => $pr['pro_no'],                     //项目号
                'tax_rate' => $pr['tax_rate'],
                'status' => 'init',
                'pro_goods_num' => $pr['tc_num'],
                'is_persent' => $reqParams['is_persent'],
                'create_at' => $now,
                'update_at' => $now,
            ];
            //dd($poItemData);

            $res = $poLogic->savePoItem($poItemData);
            if(empty($res)){
                returnJson(5020, 'savePoItem fail');
            }
            $where = ['id' => $pr['id']];
            $dataArr = [
                'is_appoint_sup' => 1,
                'appoint_sup_code' => $reqParams['supCode'],
                'appoint_sup_name' => $reqParams['supName'],
                'status' => 'wait'
            ];
            $ret = $prLogic->updatePr($where, $dataArr);
            if(empty($ret)){
                returnJson(5020, '更改PR表失败');
            }
        }
        returnJson(2000, '', $reqParams);
    }

    /*
     * 审批指定供应商状态
     */
    public function checkStatus(){
        $data = input('param.');
        $logicPrInfo = Model('RequireOrder', 'logic');
        if($data['check_status'] == 'agree'){//同意改为init状态
            $dataArr = [
                'check_status' => $data['check_status'],
                'status' => 'init'
            ];
        }else{//拒绝
            $dataArr = [
                'check_status' => $data['check_status'],
                'appoint_sup_code' => null,
                'appoint_sup_name' => null,
            ];
        }

        $where = [
            'id' => $data['id'],
        ];
        $checkStatus = [
            '' => '未审批',
            'agree' => '已同意',
            'refuse' => '已拒绝',
        ];
        $result = $logicPrInfo->updateByPrCode($where, $dataArr);
        if(false === $result){
            return json(['code' => 4000, 'msg' => '失败', 'data' => []]);
        }else{
            $status = $checkStatus[$data['check_status']];
            return json(['code' => 2000, 'msg' => '成功', 'data' => ['check_status' => $status]]);
        }
    }

    /*
     * 导出excel
     */
    function exportExcel(){
        $whereInfo = [
            'pr_code',
            'pr_date',
            'item_code',
            'item_name',
            'pro_no',
            'pur_attr',
            'status',
            'is_appoint_sup',
            'check_status'
        ];//继续筛选操作
        $get = input('param.');
        if(isset($get['pr_date']) && $get['pr_date'] !== ''){
            $get['pr_date'] = strtotime($get['pr_date']);
        }
        $where = [];
        // 应用搜索条件
        foreach($whereInfo as $key){
            if(isset($get[$key]) && $get[$key] !== ''){
                $where[$key] = ['like', "%{$get[$key]}%"];
            }
        }
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        $prLogic = Model('RequireOrder', 'logic');
        $list = $prLogic->getAllListInfo($where);
        $status = [
            'init' => '待询价',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'quoted' => '待评标',
            'flow' => '流标',
            'winbid' => '中标',
            'wait' => '待下单',
            'order' => '关闭', // 客户要求
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
            'compete' => '充分竞争',
            'have_price' => '有价格',
            'no_sup' => '无匹配供应商'
        ];
        $returnArr = [];
        foreach($list as $k => $v){
            $returnArr[] = [
                'pr_code' => $v['pr_code'],
                //请购单号
                'pr_date' => date('Y-m-d', $v['pr_date']),
                //请购日期
                'item_code' => $v['item_code'],
                //料号
                'desc' => $v['item_name'],
                //物料描述
                'pro_no' => $v['pro_no'],
                //项目号
                'tc_uom' => $v['tc_uom'],
                //交易单位
                'tc_num' => $v['tc_num'],
                //交易数量
                'price_uom' => $v['price_uom'],
                //计价单位
                'price_num' => $v['price_num'],
                //计价数量
                'req_date' => date('Y-m-d', $v['req_date']),
                //交期
                'status' => key_exists($v['status'], $status) ? $status[$v['status']] : $v['status'],
                //状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => key_exists($v['pur_attr'], self::PURATTR) ? self::PURATTR[$v['pur_attr']] : $v['pur_attr'],
                //物料采购属性
                'is_appoint_sup' => $v['is_appoint_sup'] == 1 ? '是' : '否',
                //是否指定供应商   1是 0否
                'inquiry_way' => key_exists($v['inquiry_way'], $inquiry_way) ? $inquiry_way[$v['inquiry_way']] : $v['inquiry_way'],
                //询价方式
                'check_status' => key_exists($v['check_status'], $checkStatus) ? $checkStatus[$v['check_status']] : $v['check_status'],
                //主管审批
            ];

        }
        //dump($returnArr);die;
        $list = $returnArr;
        //dump($list);die;
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('请购单列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValueExplicit('A1', '请购单号');
        $PHPSheet->setCellValueExplicit('B1', '请购日期');
        $PHPSheet->setCellValueExplicit('C1', '料号');
        $PHPSheet->setCellValueExplicit('D1', '物料描述');
        $PHPSheet->setCellValueExplicit('E1', '项目号');
        $PHPSheet->setCellValueExplicit('F1', '交易单位');
        $PHPSheet->setCellValueExplicit('G1', '交易单位数量');
        $PHPSheet->setCellValueExplicit('H1', '计价单位');
        $PHPSheet->setCellValueExplicit('I1', '计价单位数量');
        $PHPSheet->setCellValueExplicit('J1', '交期');
        $PHPSheet->setCellValueExplicit('K1', '状态');
        $PHPSheet->setCellValueExplicit('L1', '物料采购属性');
        $PHPSheet->setCellValueExplicit('M1', '是否指定供应商');
        $PHPSheet->setCellValueExplicit('N1', '询价方式');
        $PHPSheet->setCellValueExplicit('O1', '主管审批');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num + 1;
            $PHPSheet->setCellValueExplicit('A'.$num, $v['pr_code'])
                ->setCellValueExplicit('B'.$num, $v['pr_date'])
                ->setCellValueExplicit('C'.$num, $v['item_code'])
                ->setCellValueExplicit('D'.$num, $v['desc'])
                ->setCellValueExplicit('E'.$num, $v['pro_no'])
                ->setCellValueExplicit('F'.$num, $v['tc_uom'])
                ->setCellValueExplicit('G'.$num, $v['tc_num'])
                ->setCellValueExplicit('H'.$num, $v['price_uom'])
                ->setCellValueExplicit('I'.$num, $v['price_num'])
                ->setCellValueExplicit('J'.$num, $v['req_date'])
                ->setCellValueExplicit('K'.$num, $v['status'])
                ->setCellValueExplicit('L'.$num, key_exists($v['pur_attr'], self::PURATTR) ? self::PURATTR[$v['pur_attr']] : $v['pur_attr'])
                ->setCellValueExplicit('M'.$num, $v['is_appoint_sup'])
                ->setCellValueExplicit('N'.$num, $v['inquiry_way'])
                ->setCellValueExplicit('O'.$num, $v['check_status']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/prList.xlsx'); //表示在$path路径下面生成prList.xlsx文件
        $file_name = "prList.xlsx";
        $contents = file_get_contents($path.'/prList.xlsx');
        $file_size = filesize($path.'/prList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

    /*
     * 取消审核
     */
    public function cancelPoint(){
        $RequireLogic = model('RequireOrder', 'logic');

        //更改当前状态为init
        $pr_id = input('get.pr_id');
        $where = [
            'id' => $pr_id
        ];
        $data = [
            'status' => 'init',
            'is_force_inquiry' => 1
        ];
        $RequireLogic->updatePr($where, $data);
        $resAll = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/prToInquiry'));//prToInquiry
        return json($resAll);
    }

    /*
     * 重新询价
     */
    public function reInquiry(){
        $prLogic = model('RequireOrder', 'logic');

        //更改当前状态为init
        $prId = input('get.id');
        $where = $prId == 'all' ? ['inquiry_way' => 'no_sup', 'status' => 'flow'] : ['id' => $prId];
        $data = [
            'status' => 'init',
            'is_force_inquiry' => 1
        ];

        $prLogic->updatePr($where, $data);
        $resAll = json_decode(HttpService::get(getenv('APP_API_HOME').'/u9api/syncAll'));//prToInquiry
        return json($resAll);
    }
}