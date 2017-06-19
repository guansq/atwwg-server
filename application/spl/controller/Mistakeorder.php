<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 14:35
 */
namespace app\spl\controller;

use service\DataService;
use think\Db;
use controller\BasicSpl;

class Mistakeorder extends Base{
    protected $title = '异常订单';
    public function index(){
        $this->assign('title',$this->title);
        return view();
    }

    public function detail(){
        return view();
    }
}