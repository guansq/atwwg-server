<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;
use app\common\model\PoItem;
use think\Model;
use app\common\model\PoRecord;
use app\common\model\Po;
class Order extends BaseLogic{
    //获取订单中心列表
    function getOrderListInfo(){
        $list = Po::alias('a')->field('b.po_code,a.order_code,a.status,b.arv_goods_num,b.pro_goods_num,a.contract_time')->join('po_item b','a.order_code= b.po_code')->select();
       // echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
    //获取某条订单状态
    function getOrderListOneInfo($pr_code){
        $list = Po::where(['order_code'=>$pr_code])->select();

        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
    //取消订单
    function updateStatus($pr_code){
        $list = Po::where(['order_code'=>$pr_code])->update(['status'=>'sup_cancel']);

        return $list;
    }
    //更新交期时间
    function updateSupconfirmdate($pr_code,$item_code,$supconfirmdate){
        $list = PoItem::where(['po_code'=>$pr_code,'item_code'=>$item_code])->update(['sup_confirm_date'=>$supconfirmdate]);
        //echo $this->getLastSql();
        return $list;
    }
    //更新合同图片
    function updatecontract($pr_code,$src,$status){
        $list = Po::where(['order_code'=>$pr_code])->update(['status'=>$status, 'contract' => ['exp', 'concat(contract,\''.','.$src.'\')']]);
        //echo $this->getLastSql();
        //die();
        return $list;
    }
    //获取订单详情
    function getOrderDetailInfo($pr_code,$item_code=''){
        if (!empty($pr_code)){
            if(empty($item_code)){
                $list = PoItem::where(['po_code'=>$pr_code])->select();
            }else{
                $list = PoItem::where(['po_code'=>$pr_code,'item_code'=>$item_code])->select();
            }

            //echo $this->getLastSql();//die;
           // var_dump($list);
            if($list){
                $list = collection($list)->toArray();
                return $list;
            }
        }
        return false;
    }

    //获取订单记录
    function getOrderRecordInfo($pr_code,$item_code){
        if(!empty($pr_code) && !empty($item_code)){
            $list = PoRecord::where(['po_code'=>$pr_code,'item_code'=>$item_code])->select();
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