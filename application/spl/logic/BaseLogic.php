<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 11:34
 */
namespace app\spl\logic;

use think\Model;

class Baselogic extends Model{
    protected $table = 'atw_system_user';

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 生成密码
     * @param $pWd  明文密码
     * @param $salt 盐值
     */
    public static function generatePwd($pwd, $salt){
        $encryptPwd = self::encryptPwd($pwd);
        return $pwd = self::encryptPwdSalt($encryptPwd, $salt);
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 密码加密
     * @param $loginPWd
     * @param $salt
     */
    private static function encryptPwd($pwd){
        return $pwd = md5("RUITU{$pwd}KEJI");
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 密码加盐值加密
     * @param $loginPWd
     * @param $salt
     */
    private static function encryptPwdSalt($pwd, $salt = ''){
        return $pwd = sha1("THE{$salt}DAO{$pwd}");
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 校验密码
     * @param $loginUser
     * @param $password
     */
    private function checkPassword($loginUser, $encryptPwd){
        $userPwd = $loginUser->password;
        $encryptPwd = self::generatePwd($encryptPwd, $loginUser->salt);
        return $userPwd === $encryptPwd;
    }

}