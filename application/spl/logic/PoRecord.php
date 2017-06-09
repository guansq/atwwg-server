<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;

use think\Model;

class PoRecord extends Model{

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 统计已经修改过的次数
     * @param $piId
     */
    public function countByPiId($piId){
        return $this->where('pi_id',$piId)->count();
    }

}