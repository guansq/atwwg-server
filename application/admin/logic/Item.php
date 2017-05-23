<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/23
 * Time: 10:00
 */
namespace app\admin\logic;

use app\common\model\Item as ItemModel;

class Item extends Baselogic{

    /**
     * 判断U9的数据是否存在
     */
    public function exist($data){
        $count =ItemModel::field('code')->where('code',$data['code'])->count();
        return $count == 0 ? true : false;//不存在true 存在false
    }

    /**
     * 保存新增的U9数据到数据库
     */
    public function saveAllData($data){
        return model('Item')->saveAllData($data);
    }

    /*
     * 得到U9供应商数据
     */
    public function getListInfo(){
        //echo $this->getLastSql();
        /*if($list) {
            $list = collection($list)->toJson();
        }
        //dump($list);die;
        return $list;*/
    }
}