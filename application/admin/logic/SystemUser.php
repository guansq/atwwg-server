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

    /*
     * 读取 token
     */
    function getPushToken($where){
        return Db::name('SystemUser')->where($where)->value('push_token');
    }

    /*
     * 读取 头像
     */
    function getAvatar($where){
        return Db::name('SystemUser')->where($where)->value('avatar');
    }

    /*
     * 读取 用户名
     */
    function getName($where){
        return Db::name('SystemUser')->where($where)->value('user_name');
    }
}