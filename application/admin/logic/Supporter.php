<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 9:45
 */
namespace app\admin\logic;

use app\common\model\SupplierInfo as supModel;

class Supporter extends BaseLogic{

    /*
     * 得到U9供应商数据
     */
    public function getListInfo(){
        $join = [
            [],
        ];
        $list = supModel::alias('a')->field('a.id,a.code,a.type_code,a.type_name,a.status,t.arv_rate,t.pp_rate')->join('supplier_tendency t','a.code=t.sup_code','LEFT')->select();
        //echo $this->getLastSql();
        if($list) {
            $list = collection($list)->toJson();
        }
        //dump($list);die;
        return $list;
    }

    /**
     * 判断U9的数据是否存在
     */
    public function exist($data){
        $count =supModel::field('code')->where('code',$data['code'])->count();
        return $count == 0 ? true : false;//不存在true 存在false
    }

    /**
     * 保存U9到数据库
     */
    public function saveData($data){
        return model('SupplierInfo')->saveData($data);
    }

    /**
     * 保存批量数据到数据库
     */
    public function saveAllData($data){
        return model('SupplierInfo')->saveAllData($data);
    }
}