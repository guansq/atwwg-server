<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\spl\controller;

use service\LogService;
use service\DataService;
use think\Db;

class Showmsg extends Base{
    protected $title = '消息中心';

    public function index(){
        $this->assign('title',$this->title);
        return view();
    }

    public function del(){

    }

    public function add(){

    }

}