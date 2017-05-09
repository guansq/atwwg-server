<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 10:20
 */
namespace app\admin\model;
use think\Db;

class AddrModel {
    /**
     * Auther: guanshaoqiu
     * Describe: 通过pid获取子地区
     */
    public static function getAddrInfoByPid($where){
        $data = Db::name('SystemArea')->field('id,pid,name')->where($where)->select();
        return $data;
    }
}