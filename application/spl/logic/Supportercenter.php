<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 9:45
 */

namespace app\spl\logic;

use app\common\model\SupplierInfo as supModel;
use app\common\model\SupplierQualification as qualiModel;
use app\common\model\SupplierTendency as tendModel;

class Supportercenter extends BaseLogic{

    protected $table = 'atw_supplier_qualification';

    const  ADD_SCORE_QLF = ['iso90001', 'ts_lic', 'api_lic', 'ped_lic'];

    /*
     * 得到U9供应商数据
     */
    public function getListInfo($start, $length){
        //$list = supModel::alias('a')->field('a.id,a.code,a.name,a.type_code,a.type_name,a.status,t.arv_rate,t.pp_rate')->join('supplier_tendency t','a.code=t.sup_code','LEFT')->limit("$start,$length")->select();
        $list = supModel::limit("$start,$length")->select();
        if($list){
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
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     * 得到U9供应商数据总数
     */
    public function getListNum(){
        $num = supModel::alias('a')->join('supplier_tendency t', 'a.code=t.sup_code', 'LEFT')->count();
        //dump($list);die;
        return $num;
    }

    /**
     * 判断U9的数据是否存在
     */
    public function exist($data){
        $count = supModel::field('code')->where('code', $data['code'])->count();
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
    public function getOneSupInfo($sup_code){
        //缺少建立日期,技术分,责任采购,信用等级,供应风险
        /*$supinfo = supModel::alias('a')
            ->field('a.id,a.name,a.code,u.user_name,a.type_code,a.type_name,a.is_agree_purch_contract,a.pay_way_status,
            a.pay_way_change,a.state_tax_code,a.purch_contract,a.found_date,a.ctc_name,a.mobile,a.phone,a.fax,a.email,a.address,
            a.status,a.purch_type,a.check_type,a.check_rate,a.pay_way,a.pass_rate,a.arv_rate,t.arv_rate,t.pp_rate')
            ->join('supplier_tendency t','a.code=t.sup_code','LEFT')
            ->join('system_user u','a.sup_id=u.id','LEFT')
            ->where('a.code',$sup_code)->find();*/
        //echo $this->getLastSql();
        $supinfo = supModel::where('code', $sup_code)->order('update_at desc')->find();
        if($supinfo){
            $supinfo = $supinfo->toArray();
        }
        return $supinfo;
    }

    /**
     * 得到供应商图片信息
     */
    public function getSupQuali($sup_code){
        $supQuali = model('SupplierQualification')->where("sup_code", $sup_code)->select();
        if($supQuali){
            $supQuali = collection($supQuali)->toArray();
            return $supQuali;
        }
        return false;
    }

    /*
     * 检查sup_id是否存在
     */
    public function getSupId($id){
        return supModel::where('id', $id)->value('sup_id');
    }

    /*
     * 插入sup_id
     */
    public function saveSupId($id, $data){
        return supModel::where('id', $id)->update($data);
    }

    //更新合同图片
    function updatecontract($code, $src){
        $list = supModel::where(['code' => $code])->update([
            'is_agree_purch_contract' => 1,
            'purch_contract' => ['exp', 'concat(IFNULL(purch_contract,\'\'),\''.','.$src.'\')']
        ]);
        //echo $this->getLastSql();
        //die();
        return $list;
    }

    //更新支付方式
    function updatepayway($code, $payway){
        $list = supModel::where(['code' => $code])->update([
            'pay_way_change' => $payway,
            'pay_way_status' => 'uncheck'
        ]);
        //echo $this->getLastSql();
        //die();
        return $list;
    }

    //更新资质图片
    function updatesupplierqualification($sup_code, $src, $code, $begintime, $endtime,$is_forever=0){
        $now = time();
        $list = $this->where(['code' => $code, 'sup_code' => $sup_code])->update([
            'update_at' => $now,
            'status' => '',
            'term_start' => $begintime,
            'term_end' => $endtime,
            'img_src' => $src,
            'is_forever' => $is_forever
        ]);
        return $list;
    }

    //更新资质状态
    function updateSupconfirmStatus($sup_code, $code, $begintime, $endtime){
        $list = model('SupplierQualification')->where(['code' => $code, 'sup_code' => $sup_code])->update([
            'update_at' => time(),
            'status' => 'unchecked',
            'term_start' => $begintime,
            'term_end' => $endtime
        ]);
        return $list;
    }

    //查询资质图片
    function querysupplierqualification($sup_code, $code){
        $list = model('SupplierQualification')->where(['code' => $code, 'sup_code' => $sup_code])->select();
        // echo $this->getLastSql();
        //die();
        return $list;
    }

    /*
     * 得到供应商资质过期数量
     */
    public function getPastSuppNum($time, $sup_code){
        return qualiModel::where('term_end', '<', $time)
            ->where('sup_code', $sup_code)
            //->where('status', 'agree')
            ->count();
    }

    /*
     * 得到区间时间的平均到达率
     */
    public function getAvgArvRate($where, $startTime, $endTime){
        return tendModel::where($where)
            ->where('sync_date', 'between', [$startTime, $endTime])
            ->avg('arv_rate');//sync_date
    }

    /*
     * 得到区间时间的质量合格率
     */
    public function getAvgPassRate($where, $startTime, $endTime){
        return tendModel::where($where)
            ->where('sync_date', 'between', [$startTime, $endTime])
            ->avg('pass_rate');//sync_date
    }

    /*
     *资质审核数量+1
     */
    public function addOneExceed($where){
        return supModel::where($where)->setInc('qlf_check_count', 1);
        //echo $this->getLastSql();die;
    }
}