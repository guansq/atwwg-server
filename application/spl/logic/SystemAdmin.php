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

namespace app\spl\logic;
use think\Model;

/**
 * 系统管理员
 * Class Node
 * @package app\admin\model
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/14 18:12
 */
class SystemAdmin extends Model{


    /**
     * Author: W.W <will.wxx@qq.com>
     * Describe:
     * @param $username
     */
    public function findByUsername($username){
        if(empty($username)){
            return null;
        }
        return $this->where('username',$username)->find();
    }

}
