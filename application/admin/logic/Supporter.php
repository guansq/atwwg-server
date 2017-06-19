<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 9:45
 */
namespace app\admin\logic;

use app\common\model\SupplierInfo as supModel;
use app\common\model\SupplierQualification as qualiModel;
class Supporter extends BaseLogic{

    /*
     * 得到U9供应商数据
     */
    public function getListInfo($start,$length,$where = []){
        /*if(empty($where)){
            $list = supModel::alias('a')->field('a.id,a.code,a.name,a.type_code,a.type_name,a.status,a.pay_way_status,t.arv_rate,t.pp_rate')
                ->join('supplier_tendency t','a.code=t.sup_code','LEFT')->limit("$start,$length")->select();
        }else{
            $list = supModel::alias('a')->field('a.id,a.code,a.name,a.type_code,a.type_name,a.status,a.pay_way_status,t.arv_rate,t.pp_rate')
                ->where($where)->join('supplier_tendency t','a.code=t.sup_code','LEFT')->limit("$start,$length")->select();
        }*/
        if(empty($where)){
            $list = supModel::limit("$start,$length")->select();
        }else{
            $list = supModel::where($where)->limit("$start,$length")->select();
        }

        if($list) {
            $list = collection($list)->toArray();
        }
        //dump($list);die;
        return $list;
    }

    /*
     * 得到U9供应商supcode supname
     */
    public function getExcelFiledInfo($where){
        if(!empty($where)){
            //$list = supModel::field('id,code,name')->where($where)->select();
            /*$list = supModel::alias('a')->field('a.id,a.code,a.name,a.type_code,a.type_name,a.status,a.pay_way_status,t.arv_rate,t.pp_rate')
                ->join('supplier_tendency t','a.code=t.sup_code','LEFT')->where($where)->select();*/
            $list = supModel::where($where)->select();
        }else{
            /*$list = supModel::alias('a')->field('a.id,a.code,a.name,a.type_code,a.type_name,a.status,a.pay_way_status,t.arv_rate,t.pp_rate')
                ->join('supplier_tendency t','a.code=t.sup_code','LEFT')->select();*/
            $list = supModel::where($where)->select();
        }
        if($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }
    /*
     * 得到U9供应商数据总数
     */
    public function getListNum($where = []){
        if(empty($where)){
            //$num = supModel::alias('a')->join('supplier_tendency t','a.code=t.sup_code','LEFT')->count();
            $num = supModel::count();
        }else{
            //$num = supModel::alias('a')->where($where)->join('supplier_tendency t','a.code=t.sup_code','LEFT')->count();
            $num = supModel::where($where)->count();
        }
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
        /*$supinfo = supModel::alias('a')
            ->field('a.id,a.name,a.code,u.user_name,u.create_at as u_create,a.type_code,a.type_name,a.state_tax_code,a.found_date,a.ctc_name,
            a.mobile,a.phone,a.fax,a.email,a.address,a.status,a.purch_code,a.purch_name,a.purch_type,a.check_type,a.check_rate,a.pay_way,a.pay_way_change,
            a.pay_way_status,a.pass_rate,a.arv_rate,a.create_at,t.arv_rate,t.pp_rate')
            ->join('supplier_tendency t','a.code=t.sup_code','LEFT')
            ->join('system_user u','a.sup_id=u.id','LEFT')
            ->where('a.id',$sup_id)->find();*/

        $supinfo = supModel::field('a.*,u.user_name,u.create_at as u_create')->alias('a')->where('a.id',$sup_id)->join('system_user u','a.sup_id=u.id','LEFT')->order('update_at desc')->find();
        if($supinfo){
            $supinfo = $supinfo->toArray();
        }
        return $supinfo;
    }

    /**
     * 得到供应商图片信息
     */
    public function getSupQuali($sup_code){
        $list = $supQuali = model('SupplierQualification')->where("sup_code",$sup_code)->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
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

    /*
     * 更改supporterQuali
     */
    public function changeQualiStatus($where,$data){
        return qualiModel::where($where)->update($data);
    }

    /*
     * 更改suppoter
     */
    public function changeSupplierInfo($where,$data){
        return supModel::where($where)->update($data);
    }

    /*
     * 得到供应商分类
     */
    public function getTypeInfo(){
        $list = supModel::group('type_name')->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     * 获取name
     */
    public function getSupName($where){
        return supModel::where($where)->value('name');
    }

    /*
     * 得到供应商资质过期数量
     */
    public function getPastSuppNum($time){
        return qualiModel::where('term_end','<',$time)->where('status','agree')->count();
    }

    /*
     * 获取sup_id
     */
    public function getSupIdVal($where){
        return supModel::where($where)->value('sup_id');
    }

    /*
     * 更新技术分 + 5
     */
    public function updateTechScore($where){
        supModel::where($where)->setInc('tech_score', 5);
        return supModel::where($where)->setInc('qlf_score', 5);
    }
}