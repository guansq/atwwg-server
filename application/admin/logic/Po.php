<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 17:49
 */
namespace app\admin\logic;
use app\common\model\Po as poModel;

class Po extends BaseLogic{
    /*
     * 得到订单列表
     */
    function getPolist(){

        /*$join = [
            ['supplier_info b','a.sup_code=b.code','LEFT'],
            //['think_card c','a.card_id=c.id','LEFT'],
        ];*/

        //$list = poModel::alias('a')->field('a.*,b.pr_date')->join($join)->select();
        $list = poModel::select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }


}