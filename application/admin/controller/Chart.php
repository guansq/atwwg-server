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
        //echo '111111111';die;
        $this->assign('title',$this->title);
        return view();
    }

    public function qualified(){
        $this->title = '供应商质量合格率';
        //查出全部的供应商
        $this->getAllSupp();
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

}