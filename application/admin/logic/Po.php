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
     * 得到单个列表信息
     */
    function getPoInfo($id){
        $info = poModel::where('id',$id)->find();
        if($info){
            $info = $info->toArray();
        }
        return $info;
    }

    /*
     * 得到订单下的item列表
     */
    function getPoItemInfo($po_id){
        $list = poItemModel::where('po_id',$po_id)->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     * 得到即将过期的订单数量
     */
    function getPoItemNum(){
        return poItemModel::alias('a')->join('po b','a.po_id = b.id')
                ->where('b.status','in',['executing'])->where('pro_goods_num','>',0)->count();//得到执行中的订单，和订单未到货数量>0
    }

    /*
     *保存订单状态
     */
    function saveStatus($where, $data){
        return poModel::where($where)->update($data);
    }

    /*
     * 根据条件得到订单数量
     */
    public function getPoNumByWhere($where){
        return poModel::where($where)->count();
    }
}