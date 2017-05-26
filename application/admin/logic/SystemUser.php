<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 13:32
 */
namespace app\admin\logic;

use app\common\model\SystemUser as userModel;

class SystemUser extends BaseLogic{
    function saveUserInfo($data){
        model('SystemUser')->save($data);
        return model('SystemUser')->id;
    }
}