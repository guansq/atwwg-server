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

    public static function deletePoPi($code){
        self::where('order_code' ,$code)->delete();
        PoItem::where('po_code' ,$code)->delete();
    }
}