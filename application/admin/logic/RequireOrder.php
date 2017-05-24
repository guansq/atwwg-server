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

     function getPrList($start,$length){
        $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->limit("$start,$length")->select();
//        echo $this->getLastSql();
        if($list){
            $list = collection($list)->toArray();
        }
        //dump($list);
        return $list;
     }
}