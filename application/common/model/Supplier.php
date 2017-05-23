<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 10:15
 */
namespace app\common\model;
class Supplier extends BaseModel{

    public function getStatusAttr($value)
    {
        $status = [''=>'待审核',0=>'禁用',1=>'正常',2=>'待审核'];
        return $status[$value];
    }

}