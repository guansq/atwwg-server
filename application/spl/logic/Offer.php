<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;
use think\Model;
use app\common\model\Io as IoModel;

class Offer extends BaseLogic{

    function getOfferInfo($sup_code){
        $list = IoModel::where('sup_code',"$sup_code")->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
}