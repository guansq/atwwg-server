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

class Supporter extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '供应商管理';

    public function index(){
        $this->assign('title',$this->title);

        return view();
    }

    public function showSupporter(){
        echo 'test';die;
        $logic = Model('Supporter','logic');
        dump($logic);
    }

    public function read(){
        echo 'a11';
    }
    public function del(){

    }

    public function add(){

    }

    public function edit(){
        return view();
    }



}