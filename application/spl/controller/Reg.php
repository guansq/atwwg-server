<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 18:16
 */
namespace app\spl\controller;
use think\Controller;
use think\Request;
use think\Db;

class Reg extends Base{

    protected $checkLogin = false;
    /**
     * 用户登录
     * @return string
     */
    public function index(){
        return view();
    }
}