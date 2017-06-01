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
use app\common\model\Po;
use app\common\model\PoItem;

class Offer extends BaseLogic{
    //获得报价中心列表
    function getOfferInfo($sup_code,$where=''){
        if(!empty($where)){
            $list = IoModel::where('sup_code',"$sup_code")->where($where)->order('create_at desc')->select();
        }else{
            $list = IoModel::where('sup_code',"$sup_code")->order('create_at desc')->select();
        }
      //  echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
    //更改交期
    function updateData($key,$dataArr){
        $result = model('Io')->where('id',$key)->update($dataArr);
        //echo $this->getLastSql();die;
        return $result;
    }
    //获取报价单条信息
    function getOneById($Id){
        $result = IoModel::where('id',$Id)->find($Id);
        return $result;
    }
}