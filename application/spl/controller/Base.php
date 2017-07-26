<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 9:22
 */
namespace app\spl\controller;
use think\Controller;
use think\Request;
use think\Db;
use controller\BasicSpl;

class Base extends BasicSpl{
    /**
     * 页面标题
     * @var string
     */
    protected $title;

    /**
     * 默认操作数据表
     * @var string
     */
    protected $table;

    /**
     * 默认检查用户登录状态
     * @var bool
     */
    protected $checkLogin = true;

    /*public function _initialize(){
        $request = Request::instance();
        // 用户登录状态检查
        if ($this->checkLogin  && !session('spl_user')) {
            $this->redirect('@spl/login');
        }
    }*/


    /**
     * 获得请求参参数
     */
    protected function getReqParams($keys = []){
        $params = input("param.");
        $ret = [];
        //        if(empty($params)){
        //            return [];
        //        }
        if(empty($keys)){
            return $params;
        }

        foreach($keys as $k => $v){
            if(is_numeric($k)){ // 一维数组
                $ret[$v] = array_key_exists($v, $params) ? $params[$v] : '';
                continue;
            }
            $ret[$k] = array_key_exists($k, $params) ? $params[$k] : (empty($v) ? '' : $v);
        }

        return $ret;
    }
}