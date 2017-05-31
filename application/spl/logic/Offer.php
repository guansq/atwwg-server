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

    function getOfferInfo($sup_code,$where=''){
        if(!empty($where)){
            $list = IoModel::where('sup_code',"$sup_code")->where($where)->select();
          //  echo $this->getLastSql();//die;
        }else{
            $list = IoModel::where('sup_code',"$sup_code")->select();
        }

      //  echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }


    function updateData($key,$dataArr){
        $result = model('Io')->where('id',$key)->update($dataArr);
        //echo $this->getLastSql();die;
        return $result;
    }

    function getOneById($Id){
        $result = IoModel::where('id',$Id)->find($Id);
        return $result;
    }
}