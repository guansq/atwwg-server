<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\DataService;
use think\Db;

class Chart extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '';

    public function index(){
//        $res = sendMsg(295,'安特威询价单','您有新的询价单，请注意查收。');
//        $this->assign('title',$this->title);
//        dump($res);
        //return view();
    }

    public function qualified(){
        $this->title = '供应商质量合格率';
        $get = input('param.');
        $monTime = 30*24*60*60;
        $curMon = date('m');
        $monArr = getReceDateArr($curMon);//默认是最近12个月
        $where = [];
        if(isset($get['start_time']) && $get['start_time'] !== '' && isset($get['end_time']) && $get['end_time'] !== ''){
            $stTime = strtotime($get['start_time']);
            $etTime = strtotime($get['end_time']);
            if($etTime - $stTime > $monTime){
                $monArr = getMonthBetweenTime($stTime,$etTime);//满足一个月
            }
        }
        if(isset($get['sup_name']) && $get['sup_name'] !== ''){
            $sup_code = model('Supporter','logic')->getSupCode(['name'=>$get['sup_name']]);//得到sup_code
            if($sup_code){
                $where = ['sup_code'=>$sup_code];
            }
        }
        $suppLogic = model('Supporter','logic');
        $avgPassArr = [];
        $monthArr = [];
        foreach($monArr as $k => $v){
            $monthArr[$k] = date('Y-m',strtotime($v));
            $startTime = strtotime($v);
            $endTime = getEndMonthTime($v);
            $avgPassVal = keepdecimal($suppLogic->getAvgPassRate($where,$startTime,$endTime));//合格率
            $avgPassVal = initPerVal($avgPassVal, true, false)*1;//转化百分比
            $avgPassArr[$k] = $avgPassVal;
        }
        //得到数据
        //dump($avgPassArr);
        //dump($monthArr);
        //查出全部的供应商$this->getAllSupp();
        //dump(getMonthBetweenTime(strtotime('2015-06-01'),strtotime('2017-06-01')));
        $this->assign('avgPassArr',json_encode(array_reverse($avgPassArr)));
        $this->assign('monthArr',json_encode(array_reverse($monthArr)));
        $this->assign('title',$this->title);
        return view();
    }

    public function time(){
        $this->title = '供应商交货及时率';
        $get = input('param.');
        $monTime = 30*24*60*60;
        $curMon = date('m');
        $monArr = getReceDateArr($curMon);//默认是最近12个月
        $where = [];
        if(isset($get['start_time']) && $get['start_time'] !== '' && isset($get['end_time']) && $get['end_time'] !== ''){
            $stTime = strtotime($get['start_time']);
            $etTime = strtotime($get['end_time']);
            if($etTime - $stTime > $monTime){
                $monArr = getMonthBetweenTime($stTime,$etTime);//满足一个月
            }
        }
        if(isset($get['sup_name']) && $get['sup_name'] !== ''){
            $sup_code = model('Supporter','logic')->getSupCode(['name'=>$get['sup_name']]);//得到sup_code
            if($sup_code){
                $where = ['sup_code'=>$sup_code];
            }
        }
        $suppLogic = model('Supporter','logic');
        //$avgPassArr = [];
        $avgArvArr = [];
        $monthArr = [];
        foreach($monArr as $k => $v){
            $monthArr[$k] = date('Y-m',strtotime($v));
            $startTime = strtotime($v);
            $endTime = getEndMonthTime($v);

            $avgArvVal = keepdecimal($suppLogic->getAvgArvRate($where,$startTime,$endTime));//及时率
            $avgArvVal = initPerVal($avgArvVal, true, false)*1;//转化百分比
            $avgArvArr[$k] = $avgArvVal;
        }
        $this->assign('avgArvArr',json_encode(array_reverse($avgArvArr)));
        $this->assign('monthArr',json_encode(array_reverse($monthArr)));
        $this->assign('title',$this->title);
        return view();
    }

    public function getAllSupp(){

    }

    //public function

}