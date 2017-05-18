<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/18
 * Time: 16:21
 */
namespace app\common\model;

class SystemBanner extends BaseModel{

    public static function getAllList(){
        return self::field('id,name,src,sort')->order('sort asc')->select();
    }
}