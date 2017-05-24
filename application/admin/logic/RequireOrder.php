<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 9:57
 */
namespace app\admin\logic;

use app\common\model\U9Pr as prModel;

class RequireOrder extends BaseLogic{

    /*
     * 得到请购单信息
     */
     function getPrList($start,$length){
        $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->limit("$start,$length")->select();
//        echo $this->getLastSql();
        if($list){
            $list = collection($list)->toArray();
        }
        //dump($list);
        return $list;
     }

    /*
     * 得到列表数量
     */
     function getListNum(){
         $count = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->count();
         return $count;
     }

     /*
      *根据itemcode得到供应商物料交叉表信息
      */
     function getSupList($item_code){
        $list = model('U9SupItem')->where('item_code',$item_code)->select();
         if($list){
             $list = collection($list)->toArray();
         }
         //dump($list);
         return $list;
     }

     /*
      * 保存唯一指定供应商到表
      */
     function updateByPrCode($pr_code,$where){
         return model('U9Pr')->where($where)->update(['pr_code'=>$pr_code]);
     }
}