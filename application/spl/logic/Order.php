<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;
use app\common\model\PoItem;
use app\common\model\PoRecord;
use app\common\model\Po;
class Order extends BaseLogic{
    /*
     * 得到订单列表
     */
    function getPolist($where){
        if(empty($where)){
            $list = Po::select();
        }else{
            $list = Po::where($where)->select();
        }
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     * 得到单个列表信息
     */
    function getPoInfo($id){
        $info = Po::where('id',$id)->find();
        if($info){
            $info = $info->toArray();
        }
        return $info;
    }
    /*
    * 得到订单下的item列表
    */
    function getPoItemInfo($po_id){
        $list = PoItem::where('po_id',$po_id)->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
    //获取订单中心列表

    /*function getOrderListInfo($sup_code=''){
        $list = Po::alias('po')->field('pi.po_code,po.order_code,po.status,pi.arv_goods_num,pi.pro_goods_num,po.contract_time,pi.item_code')->join('po_item pi','po.id = pi.po_id')->where(['sup_code'=>$sup_code])->order('po.create_at desc')->select();
        //echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }*/
    //获取某条订单状态
    function getOrderListOneInfo($id){
        $list = Po::where(['id'=>$id])->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改订单状态
    function updateStatus($id,$status='sup_cancel'){
        $list = Po::where(['id'=>$id])->update(['status'=>$status]);
        return $list;
    }
    //更新交期时间
    function updateSupconfirmdate($po_id,$supconfirmdate){
        $list = PoItem::where(['po_id'=>$po_id])->update(['sup_confirm_date'=>$supconfirmdate,'status'=>'uncheck']);
        //echo $this->getLastSql();
        return $list;
    }
    //更新合同图片
    function updatecontract($id,$src,$status){
        $list = Po::where(['id'=>$id])->update(['status'=>$status, 'contract' => ['exp', 'concat(IFNULL(contract,\'\'),\''.','.$src.'\')']]);
        //echo $this->getLastSql();
        //die();
        return $list;
    }
    //获取订单详情
    function getOrderDetailInfo($po_id,$item_code=''){
        if (!empty($po_id)){
            if(empty($item_code)){
                $list = PoItem::where(['po_id'=>$po_id])->select();
            }else{
                $list = PoItem::where(['po_id'=>$po_id,'item_code'=>$item_code])->select();
            }
            //echo $this->getLastSql();//die;
            if($list){
                $list = collection($list)->toArray();
                return $list;
            }
        }
        return false;
    }

    //获取订单记录
    function getOrderRecordInfo($id){
        if(!empty($id) ){
            $list = PoRecord::where(['pi_id'=>$id])->select();
            //echo $this->getLastSql();//die;
            if($list){
                $list = collection($list)->toArray();
                return $list;
            }
        }
        // var_dump($list);
        return false;
    }

}