<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 */

namespace app\common\model;

class Po extends BaseModel{

    public static function findByCode($code){
       return self::where('order_code' ,$code)->find();
    }
}