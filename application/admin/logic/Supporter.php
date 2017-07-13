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
use app\common\model\SupplierTendency as tendModel;
class Supporter extends BaseLogic{

    protected $table ='atw_supplier_info';
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
     * 得到sup_id是否存在
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
        return qualiModel::where('term_end','<',$time)
            //->where('status','agree')
            ->count();
    }

    /*
     * 得到资质过期 的 供应商 数量
     */
    public function countPastSupNum(){
        return $this->where('qlf_exceed_count','>',0)->count();
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
        supModel::where($where)->setInc('tech_score', 5*0.4);//技术分5*0.4
        return supModel::where($where)->setInc('qlf_score', 5);
    }

    /*
    * 得到sup_code
    */
    public function getSupCode($where){
        return supModel::where($where)->value('code');
    }
    /*
     * 得到区间时间的平均到达率
     */
    public function getAvgArvRate($where,$startTime,$endTime){
        if(empty($where)){
            return tendModel::where('sync_date','between',[$startTime,$endTime])->avg('arv_rate');//sync_date
        }
        return tendModel::where($where)->where('sync_date','between',[$startTime,$endTime])->avg('arv_rate');//sync_date
    }

    /*
     * 得到区间时间的质量合格率
     */
    public function getAvgPassRate($where,$startTime,$endTime){
        if(empty($where)){
            return tendModel::where('sync_date','between',[$startTime,$endTime])->avg('pass_rate');//sync_date
        }
        return tendModel::where($where)->where('sync_date','between',[$startTime,$endTime])->avg('pass_rate');//sync_date
    }
    /*
     * 得到证书结束时间
     */
    public function getEndTime($where){
        return qualiModel::where($where)->where('status','agree')->value('term_end');
    }
    /*
     * 得到未审核的商家数量
     */
    public function countQlfUnchecked(){
        return $this->where('qlf_check_count','>',0)->count();
        //echo $this->getLastSql();//return
    }

    /*
     * 統計高信用風險供应商数量
     */
    public function countCreditRisk(){
        return $this->where('credit_total','<=',85)->count();
        //echo $this->getLastSql();//return
    }

    /*
     * 得到所有的供应商code 以及供应商名称
     */
    public function getSupNameAndCode(){
        $list = supModel::field('code,name')->where('code','neq','')->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     *资质审核数量-1
     */
    public function subOneExceed($where){
        return supModel::where($where)->setDec('qlf_check_count',1);
    }

    /*
     * 通过sup_code 得到所需发送的phone mail token
     */
    public function getSupSendInfo($where){
        $info = supModel::field('a.phone,a.email,u.push_token')->alias('a')->join('system_user u','a.sup_id=u.id','LEFT')->where($where)->find();
        if($info){
            $info = $info->toArray();
        }
        return $info;
    }
}