<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 10:15
 */
namespace app\common\model;
class Item extends BaseModel{

    /**
     * 保存全部数据
     */
    public function saveAllData($data){
        return $this->allowField(true)->saveAll($data);
    }
}