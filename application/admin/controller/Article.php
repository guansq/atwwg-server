<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 14:08
 */
namespace app\admin\controller;

use service\LogService;
use service\DataService;

class Article extends BaseController{
    protected $table = 'SystemBanner';
    protected $title = '文章管理';

    function index(){

        $this->assign('title',$this->title);
        return view();
    }

    function add(){
        return view();
    }

    function del(){
        return view();
    }
}