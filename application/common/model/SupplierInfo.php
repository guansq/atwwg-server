<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 10:54
 */
namespace app\common\model;

use think\Db;

class SupplierInfo extends BaseModel{

    /**
     * 保存数据
     */
    public function saveData($data){
        return $this->allowField(true)->save($data);
    }

    /**
     * 保存全部数据
     */
    public function saveAllData($data){
        return $this->allowField(true)->saveAll($data);
    }
}