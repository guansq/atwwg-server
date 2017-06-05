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

class Showmsg extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '相关信息';

    public function index(){
        $current_time = time();
        //询价待审批

        //订单逾期警告

        //供应商资质过期

        //流拍询价数量

        //运营情况一览表

        $this->assign('title',$this->title);
        return view();
    }

    public function del(){

    }

    public function add(){

    }

}