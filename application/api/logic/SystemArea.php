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

namespace app\api\logic;
use \think\Db;
use app\common\model\SystemArea as SystemAreaModel;
/**
 * 系统权限节点读取器
 * Class Node
 * @package app\admin\model
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/14 18:12
 */
class SystemArea extends BaseLogic{

    /**
     * 获取授权节点
     * @staticvar array $nodes
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAddrList($pid='',$name='') {
        //$this->title = '地区管理';
        if(empty($name)){
            $where = ['pid'=>$pid];
        }else{
            $where = ['name'=>['like','%'.$name.'%']];
        }
        //dump($where);
        $addr = SystemAreaModel::getList($where);
        return $addr;
    }

}
