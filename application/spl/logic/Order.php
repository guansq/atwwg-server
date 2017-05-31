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

use app\common\model\Po;
class Order extends BaseLogic{

    function getOrderListInfo(){
        $list = Po::alias('a')->field('b.po_code,a.order_code,a.status,b.arv_goods_num,b.pro_goods_num,a.contract_time')->join('po_item b','a.order_code= b.po_code')->select();
        //echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
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
    function updatecontract($pr_code,$src){
        $list = Po::where(['order_code'=>$pr_code])->update([ 'contract' => ['exp', 'concat(contract,\''.','.$src.'\')']]);
        //echo $this->getLastSql();
        //die();
        return $list;
    }




    function getOrderDetailInfo($pr_code){
        if (!empty($pr_code)){
            $list = PoItem::where(['po_code'=>$pr_code])->select();
//            $list = Po::alias('a')->field('a.pr_code,	a.order_code,	c.item_code,	c.item_name,	c.tc_num,	c.tc_uom,	b.sup_confirm_date,  d.quote_price,	d.promise_date,	b.arr_goods_num,	b.pro_goods_num')
//                ->join('atw_po_item b','a.order_code= b.po_code') ->join('atw_u9_pr c','a.pr_code = c.pr_code AND b.item_code = c.item_code')
//                ->join('atw_io d','d.pr_code=a.pr_code') ->where(['order_code'=>$pr_code])->select();

            //echo $this->getLastSql();//die;
           // var_dump($list);
            if($list){
                $list = collection($list)->toArray();
                return $list;
            }
        }
        return false;

    }



}