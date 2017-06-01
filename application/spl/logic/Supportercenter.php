<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 9:45
 */
namespace app\admin\logic;

use app\common\model\SupplierInfo as supModel;

class Supportercenter extends BaseLogic{

    /*
     * 得到U9供应商数据
     */
    public function getListInfo($start,$length){
        $list = supModel::alias('a')->field('a.id,a.code,a.name,a.type_code,a.type_name,a.status,t.arv_rate,t.pp_rate')->join('supplier_tendency t','a.code=t.sup_code','LEFT')->limit("$start,$length")->select();

        if($list) {
            $list = collection($list)->toArray();
        }
        //dump($list);die;
        return $list;
    }

    /*
     * 得到U9供应商supcode supname
     */
    public function getExcelFiledInfo(){
        $list = supModel::field('id,code,name')->select();
        if($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }
    /*
     * 得到U9供应商数据总数
     */
    public function getListNum(){
        $num = supModel::alias('a')->join('supplier_tendency t','a.code=t.sup_code','LEFT')->count();
        //dump($list);die;
        return $num;
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
            ->field('a.id,a.name,a.code,u.user_name,a.type_code,a.type_name,a.state_tax_code,a.found_date,a.ctc_name,a.mobile,a.fax,a.email,a.address,a.status,a.purch_type,a.check_type,a.check_rate,a.pay_way,t.arv_rate,t.pp_rate')
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

    /*
     * 检查sup_id是否存在
     */
    public function getSupId($id){
        return supModel::where('id',$id)->value('sup_id');
    }

    /*
     * 插入sup_id
     */
    public function saveSupId($id,$data){
        return supModel::where('id',$id)->update($data);
    }
}