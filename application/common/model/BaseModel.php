<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 10:15
 */
namespace app\common\model;
use think\Model;
class BaseModel extends Model{

    /*public function save($data = [], $where = [], $sequence = null){
        if(empty($data)){
            $data['create_at']=time();
        }
        $data['update_at']=time();
        return parent::save($data, $where, $sequence);
    }*/

}