<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 11:34
 */

namespace app\spl\logic;

use think\Model;

class BaseLogic extends Model{

    /**
     * 写入数据
     * @access public
     * @param array      $data  数据数组
     * @param array|true $field 允许字段
     * @return $this
     */
    public static function create($data = [], $field = null){
        $data['create_at'] = $data['update_at'] = time();
        return parent::create($data, $field);
    }

    /**
     * 更新数据
     * @access public
     * @param array      $data  数据数组
     * @param array      $where 更新条件
     * @param array|true $field 允许字段
     * @return $this
     */
    public static function update($data = [], $where = [], $field = null){
        $data['update_at'] = time();
        return parent::update($data, $where, $field);
    }

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