<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/15
 * Time: 17:38
 */
namespace app\common\model;
class MessageSendee extends BaseModel{

    public function saveSendee($data){
        return $this->save($data);
    }
}