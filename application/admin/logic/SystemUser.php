<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/26
 * Time: 13:32
 */
namespace app\admin\logic;

use app\common\model\SystemUser as userModel;
use think\Db;

class SystemUser extends BaseLogic{
    function saveUserInfo($data){
        Db::name('SystemUser')->insert($data);
        return Db::name('SystemUser')->getLastInsID();
    }
}