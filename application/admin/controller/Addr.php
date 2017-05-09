<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use think\db;

class Addr extends BasicAdmin{
    protected $table = 'SystemArea';
    protected $title = '网站地区管理';

    public function index(){
        echo 'test';
    }
}