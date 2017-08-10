<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;


class Item extends BaseLogic{
    protected $table = 'atw_item';

    /**
     * 根据code查找
     */
    public static function findByCode($code){
        return self::where('code', $code)->find();
    }
}