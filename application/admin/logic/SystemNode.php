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
use \think\Db;

/**
 * 系统权限节点读取器
 * Class Node
 * @package app\admin\model
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/14 18:12
 */
class SystemNode extends BaseLogic{


    /**
     * 应用用户权限节点
     * @return bool
     */
    public static function applyAuthNode() {
        cache('need_access_node', null);
        if (($authorize = session('user.authorize'))) {
            $authorizeids = Db::name('SystemAuth')->where('id', 'in', explode(',', $authorize))->where('status', '1')->column('id');
            if (empty($authorizeids)) {
                return session('user.nodes', []);
            }
            $nodes = Db::name('SystemAuthNode')->where('auth', 'in', $authorizeids)->column('node');
            return session('user.nodes', $nodes);
        }
        return false;
    }
    /**
     * 获取授权节点
     * @staticvar array $nodes
     * @return array
     */
    public static function getAuthNode() {
        static $nodes = [];
        if (empty($nodes)) {
            $nodes = cache('need_access_node');
            if (empty($nodes)) {
                $nodes = Db::name('SystemNode')->where('is_auth', '1')->column('node');
                cache('need_access_node', $nodes);
            }
        }
        return $nodes;
    }


    /**
     * 检查用户节点权限
     * @param string $node 节点
     * @return bool
     */
    public static function checkAuthNode($node) {
        $auth_node = strtolower($node);
        if (session('user.username') === 'admin' || stripos($node, 'admin/index') === 0) {
            return true;
        }
        if (!in_array($auth_node, self::getAuthNode())) {
            return true;
        }
        return in_array($auth_node, (array) session('user.nodes'));
    }

}
