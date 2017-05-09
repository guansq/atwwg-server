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
    public static function getAddrInfo($where){
        $data = Db::name('SystemArea')->field('id,pid,name,merger_name,level')->where($where)->select();
        return $data;
    }

    public static function haveChild($id){
        $count = Db::name('SystemArea')->where('pid',$id)->count();
        //echo Db::name('SystemArea')->getLastSql();
        return $count;
    }
}