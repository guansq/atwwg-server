<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/14
 * Time: 10:53
 */
namespace app\admin\controller;

class Scoreset extends BaseController{
    protected $title = '分值设置';
    public function index(){
        $this->assign('title',$this->title);
        return view();
    }
}