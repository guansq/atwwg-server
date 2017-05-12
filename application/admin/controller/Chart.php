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
use app\admin\model\AddrModel;
use think\Db;

class Chart extends Base{
    protected $table = 'SystemArea';
    protected $title = '';

    public function index(){
        //echo '111111111';die;
        $this->assign('title',$this->title);
        return view();
    }

    public function qualified(){
        $this->title = '供应商质量合格率';
        $this->assign('title',$this->title);
        return view();
    }

    public function time(){
        $this->title = '供应商交货及时率';
        $this->assign('title',$this->title);
        return view();
    }

}