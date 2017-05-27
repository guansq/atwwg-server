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
    function getPolist($where){
        if(empty($where)){
            $list = poModel::select();
        }else{
            $list = poModel::where($where)->select();
        }
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }


}