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

class Suppliercenter extends BasicSpl{

    public function index(){
        return view();
    }
}