<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 17:49
 */

namespace app\admin\logic;

use app\common\model\Po as poModel;
use app\common\model\PoItem as poItemModel;
use service\HttpService;

class Po extends BaseLogic{
    protected $table = 'atw_po_item';

    const U9_BIZ_TYPES = [
        '0' => 'PO01',  //标准类型
        '1' => 'PO14',  //全程委外
        '2' => 'PO16',  //机加委外
        '3' => 'PO12',  //工序外协
    ];


    /*
     * 得到订单列表
     */
    function getPolist($where){
        if(empty($where)){
            $list = poModel::order('update_at DESC')->select();
        }else{
            $list = poModel::where($where)->order('update_at DESC')->select();
        }
        return $list;
    }

    /*
     * 得到poList的数量
     */
    public function getPoCount(){
        $count = poModel::count();
        return $count;
    }

    /*
     * 得到单个列表信息
     */
    function getPoInfo($id){
        $info = poModel::where('id', $id)->find();
        if($info){
            $info = $info->toArray();
            $info['contract'] = empty($info['contract']) ? [] : explode(',', $info['contract']);
        }
        return $info;
    }

    /*
     * 得到订单下的item列表
     */
    function getPoItemInfo($po_id){
        $list = poItemModel::where('po_id', $po_id)->field('*, "" AS pro_no ')->select();
        foreach($list as &$pi){
            $pi['pro_no'] = model('RequireOrder', 'logic')->where('id', $pi->pr_id)->value('pro_no');
        }
        return $list;
    }

    /*
     * 得到即将过期的订单数量
     */
    function getPoItemNum(){
        $count = poItemModel::alias('pi')
            ->join('po po', 'pi.po_id = po.id')
            ->where('po.status', 'NOT IN', [
                'finish',
                'sup_cancel'
            ])
            ->where('pi.pro_goods_num', '>', 0)
            ->where('pi.sup_confirm_date', '<', time())
            ->group('po.id')
            ->count();//得到执行中的订单，和订单未到货数量>0
        return $count;
    }

    /*
     *保存订单状态
     */
    function saveStatus($where, $data){
        return poModel::where($where)->update($data);
    }

    /*
     *保存明细订单状态
     */
    function saveItemInfo($where, $data){
        $dbRet = poItemModel::where($where)->update($data);
        return $dbRet;
    }

    /*
     * 根据条件得到订单数量
     */
    public function getPoNumByWhere($where){
        return poModel::where($where)->count();
    }

    /*
     * 得到订单状态
     */
    public function getPoStatus($where){
        return poModel::where($where)->value('status');
    }

    /*
     * 得到poItemList
     */
    public function getPoItemList($where){
        if(empty($where)){
            $list = poItemModel::where('status', 'init')->order('update_at DESC')->field('*, "" AS pro_no ')->select();
        }else{
            $list = poItemModel::where($where)
                ->where('status', 'init')
                ->order('update_at DESC')
                ->field('*, "" AS pro_no ')
                ->select();
        }
        foreach($list as &$pi){
            $pi['pro_no'] = model('RequireOrder', 'logic')->where('id', $pi->pr_id)->value('pro_no');
        }
        return $list;
    }

    /*
     * 得到poItemList的数量
     */
    public function getPoItemCount(){
        $count = poItemModel::where('status', 'init')->count();
        return $count;
    }

    /*
    * 得到订单生成日期
    */
    public function getPoCreateat($where){
        return poModel::where($where)->value('create_at');
    }

    /*
     * 得到po_id
     */
    public function getPoId($where){
        return poItemModel::where($where)->value('po_id');
    }

    /*
     * 得到order_code
     */
    public function getOrderCode($where){
        return poModel::where($where)->value('order_code');
    }

    /*
     * 手动生成订单
     */
    public function createOrder(){

    }

    /*
     * 得到供应商code 得到供应商名称
     */
    public function getSupInfo($where){
        $info = poItemModel::field('sup_code,sup_name')->where($where)->find();
        if($info){
            $info = $info->toArray();
        }
        return $info;
    }

    /*
     * 得到poitemInfo
     */
    public function getPoItem($id){
        $info = poItemModel::where('id', $id)->find();
        if($info){
            $info = $info->toArray();
        }
        return $info;
    }

    /*
     * 得到OrderId
     */
    public function insertOrGetId($poData){
        //$findPo = poModel::where('pr_code', $poData['pr_code'])->where('sup_code', $poData['sup_code'])->find();
        //if(empty($findPo)){} return $findPo['id'];
        return poModel::insertGetId($poData);
    }

    /*
     * 批量更新ID
     */
    public function updateAllPoid($list){
        $res = $this->saveAll($list);
        return $res;
    }

    /*
     * 得到订单记录
     */
    public function getPoItemByIds($idWhere){
        $list = poItemModel::where('id', 'in', $idWhere)->where('status', 'init')->select();
        return $list;
    }

    /*
     * 保存poItem
     */
    public function savePoItem($data){
        return $res = poItemModel::create($data);
    }

    /**
     * u9下采购单返回的$rtnLines 匹配 pi
     */
    function matePoLn($rtnLines, $pi){
        if(empty($rtnLines) || empty($pi)){
            return null;
        }
        foreach($rtnLines as $k => $v){
            if(!is_numeric($k)){
                $v = $rtnLines;
            }
            if($v['srcDocNo'] == $pi['pr_code'] && $v['srcLineNo'] == $pi['pr_ln']){
                return $v['LineNo'];
            }
            if(!is_numeric($k)){
                break;
            }
        }
        return null;
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: u9下采购单
     * @param $idArr
     * @param $supCode
     */
    function placePoOrder($idArr, $supCode){
        trace("u9下采购单 ====== placePoOrder \n");
        trace("idArr ======".json_encode($idArr));
        $now = time();
        $supLogic = model('Supporter', 'logic');
        $prLogic = model('RequireOrder', 'logic');
        foreach(self::U9_BIZ_TYPES as $bizType => $docTypeCode){
            //进行生成订单
            $itemInfo = $this->getPiByIdsAndBizType($idArr, $bizType);//单个子订单信息

            trace(json_encode($itemInfo));
            if(empty($itemInfo)){
                continue;
            }
            $res = $this->placeOrderAll($itemInfo, $docTypeCode);//内部生成订单
            //dump($res);die;
            $data = [];
            //dump($res);die;

            if($res['code'] != 2000){
                return resultArray(6000, '下订单失败', $data);
            }
            //生成一条po记录
            $poData = [
                //'pr_code' => $itemInfo['pr_code'],
                'order_code' => $res['data']['DocNo'],
                'sup_code' => $supCode,
                'doc_date' => $now,
                'is_include_tax' => 1,      //是否含税
                'status' => 'init',
                'create_at' => $now,
                'update_at' => $now,
            ];
            $po_id = $this->insertOrGetId($poData);
            //生成关联关系
            $list = [];
            $rtnPoLine = empty($res['data']['rtnLines']['rtnPoLine']) ? [] : $res['data']['rtnLines']['rtnPoLine'];
            foreach($itemInfo as $pi){
                $list[] = [
                    'id' => $pi['id'],
                    'po_id' => $po_id,
                    'po_code' => $res['data']['DocNo'],
                    'po_ln' => $this->matePoLn($rtnPoLine, $pi),
                    'update_at' => $now,
                    'status' => 'placeorder'
                ];

            }
            /*foreach($idArr as $k=>$v){
                $data[$k] = ['id'=>$v,'po_id'=>$po_id,'po_code'=>$res['data']['DocNo'],'create_at'=>date('Y-m-d',$now)];
            }*/
            $res = $this->updateAllPoid($list);
            $data = $list;
            //更改PR表status状态为已下单

            foreach($itemInfo as $k => $v){
                $prLogic->updatePr(['id' => $v['pr_id']], ['status' => 'order']);
            }
        }

        //发消息通过$sup_code $sup_name得到$sup_id
        $sup_id = $supLogic->getSupIdVal(['code' => $supCode]);
        if(empty($sup_id)){
            return resultArray(5000, "下订单成功，消息发送失败。 code:$supCode 未绑定账号。",$data);
        }
        sendMsg($sup_id, '安特威订单', '您有新的订单，请注意查收。');//发送消息
        return resultArray(2000,'下订单成功',$data);

    }

    /**
     * 内部创建U9订单
     */
    public function placeOrderAll($itemInfo, $docTypeCode = 'PO01'){
        $prLogic = model('RequireOrder', 'logic');
        $sendData = [];
        $sendData['DocDate'] = time();//单价日期
        $sendData['DocTypeCode'] = 'PO01';//单据类型
        $sendData['TCCode'] = 'C001';//币种编码
        $sendData['bizType'] = '316';//U9参数
        $sendData['isPriceIncludeTax'] = 1;         //  是否含税
        $sendData['DocTypeCode'] = $docTypeCode;    //  采购订单单据类型
        $sendData['supplierCode'] = $itemInfo[0]['sup_code'];//供应商代码
        $lines = [];
        foreach($itemInfo as $k => $v){
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
                'ProCode' => $prLogic->where('id', $v['pr_id'])->value('pro_no'),
                'srcDocPRNo' => $v['pr_code'],

            ];
        }
        $sendData['lines'] = $lines;
        //exit(json_encode($sendData));
        $httpRet = HttpService::curl(getenv('APP_API_U9').'index/po', $sendData);
        $res = json_decode($httpRet, true);//成功回写数据库
        if($res['code'] != 2000){
            returnjson($res);
        }
        //dump($res['result']);die;
        return ['code' => 2000, 'msg' => '', 'data' => $res['result']];
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe:
     * @param $piIds
     * @param $bizType
     */
    function getPiByIdsAndBizType($piIds, $bizType){
        $list = $this->alias('pi')
            ->join('u9_pr pr', 'pi.pr_id = pr.id')
            ->where('pi.id', 'IN', $piIds)
            ->where('pr.biz_type', $bizType)
            ->order('pi.update_at', 'DESC')
            ->field('pi.*')
            ->select();
        return $list;
    }
}