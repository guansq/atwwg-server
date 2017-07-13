<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;

use think\Model;

class SupplierInfo extends Model{

    public function findByCode($code){
        return $this->where(['code' => $code])->find();
    }
}