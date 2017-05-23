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

    /**
     * 得到单个供应商信息
     */
    public function getOneSupInfo($sup_id){
        //缺少建立日期,技术分,责任采购,信用等级,供应风险
        $supinfo = supModel::alias('a')
            ->field('a.id,a.name,a.code,u.user_name,a.type_code,a.type_name,a.tax_code,a.found_date,a.ctc_name,a.mobile,a.fax,a.email,a.address,a.status,a.purch_type,a.check_type,a.check_rate,t.arv_rate,t.pp_rate')
            ->join('supplier_tendency t','a.code=t.sup_code','LEFT')
            ->join('system_user u','a.sup_id=u.id','LEFT')
            ->where('a.id',$sup_id)->find();
        if($supinfo){
            $supinfo = $supinfo->toArray();
        }
        return $supinfo;
    }

    /**
     * 得到供应商图片信息
     */
    public function getSupQuali($sup_code){
        return $supQuali = model('SupplierQualification')->where("sup_code",$sup_code)->select();
    }
}