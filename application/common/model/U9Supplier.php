<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 10:54
 */
namespace app\common\model;

use think\Db;

class U9Supplier extends BaseModel{

    /**
     * 获取供应商信息
     */
    public static function getSupporterInfo(){
        return self::select();
    }

}