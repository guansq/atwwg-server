<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 9:45
 */
namespace app\admin\logic;

use app\Common\model\U9Supplier as U9supModel;

class U9Supporter extends BaseLogic{

    /**
     * 取得U9信息
     */
    public function getListInfo(){
        $list = U9supModel::all();
        if($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }


}