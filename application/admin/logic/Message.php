<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 9:57
 */

namespace app\admin\logic;

use app\common\model\Message as MessModel;

use app\common\model\MessageSendee as MessSendeeModel;

class Message extends BaseLogic{
    public function getMessNum(){
        return MessModel::count();
    }
}