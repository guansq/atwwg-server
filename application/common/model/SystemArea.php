<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 10:20
 */
namespace app\common\model;

class SystemArea extends BaseModel{

    /**
     * Auther: guanshaoqiu
     * Describe: 通过pid获取子地区
     */
    public static function getList($where){
        $data = self::field('id,pid,name,merger_name,level')->where($where)->select();
        return $data;
    }

    public static function haveChild($id){
        $count = self::where('pid',$id)->count();
        return $count;
    }
}