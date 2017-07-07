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

class Po extends BaseLogic{
    protected $table = 'atw_po_item';

    /*
     * 得到订单列表
     */
    function getPolist($where){
        if(empty($where)){
            $list = poModel::order('update_at DESC')->select();
        }else{
            $list = poModel::where($where)->order('update_at DESC')->select();
        }
        if($list){
            $list = collection($list)->toArray();
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
        $list = poItemModel::where('po_id', $po_id)->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     * 得到即将过期的订单数量
     */
    function getPoItemNum(){
        return poItemModel::alias('a')
            ->join('po b', 'a.po_id = b.id')
            ->where('b.status', 'in', ['executing'])
            ->where('pro_goods_num', '>', 0)
            ->count();//得到执行中的订单，和订单未到货数量>0
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
            $list = poItemModel::where('status', 'init')->order('update_at DESC')->select();
        }else{
            $list = poItemModel::where($where)->where('status', 'init')->order('update_at DESC')->select();
        }
        if($list){
            $list = collection($list)->toArray();
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
        if($res){
            $res = collection($res)->toArray();
        }
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
            if($v['srcDocNo']==$pi['pr_code'] && $v['srcLineNo'] ==$pi['pr_ln'] ){
                return $v['LineNo'];
            }
            if(!is_numeric($k)){
                break;
            }
        }
        return null;
    }
}