<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 16:59
 */
namespace app\admin\controller;
use think\Controller;
use think\Request;

class Base extends Controller{
    public function _initialize(){
        $request = Request::instance();
        $aid = is_login();
    }
}