<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/15
 * Time: 17:38
 */
namespace app\common\model;
class Message extends BaseModel{

    public function saveMsg($data){
        return $this->insertGetId($data);
    }
}