<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;
use think\Model;

use app\common\model\Po;
class Order extends BaseLogic{

    function getOrderListInfo(){
        $list = Po::alias('a')->field('b.po_code,a.order_code,a.status,b.arr_goods_num,b.pro_goods_num,a.contract_time')->join('po_item b','a.order_code= b.po_code')->select();
        //echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }


}