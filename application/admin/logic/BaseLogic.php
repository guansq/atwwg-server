<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\admin\logic;

use think\Model;

/**
 */
class BaseLogic extends Model{
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
    /*
     * 通过pr_code得到pr_date
     */
    function getPrDate($pr_code){
        return model('U9Pr')->where('pr_code',$pr_code)->value('pr_date');
    }

    /*
     * 通过sup_code得到sup_name
     */
    function getSupName($sup_code){
        return model('supplier_info')->where('code',$sup_code)->value('name');
    }
}
