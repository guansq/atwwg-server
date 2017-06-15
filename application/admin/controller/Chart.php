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
        //查出全部的供应商
        $this->getAllSupp();
        echo date('m');//06
        echo date('y');//17
        //往前推12月
//        $start = '2017年05月05日';
//        $end = '2017年08月10日';
//        prDates($start,$end);
        $this->assign('title',$this->title);
        return view();
    }

    public function time(){
        $this->title = '供应商交货及时率';
        //查出全部的供应商
        $this->assign('title',$this->title);
        return view();
    }

    public function getAllSupp(){

    }

    //public function

}