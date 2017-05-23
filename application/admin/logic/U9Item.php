<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/23
 * Time: 9:59
 */
namespace app\admin\logic;

use app\common\model\U9Item as U9ItemModel;

class U9Item extends BaseLogic{

    public function getListInfo(){
        $list = U9ItemModel::all();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }


}