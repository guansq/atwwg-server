<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 9:57
 */
namespace app\admin\logic;

use app\common\model\Io as IoModel;

class Io extends BaseLogic{

     function getIoList($start,$length){

        $list = IoModel::alias('a')->field('a.*,b.desc,c.pro_no,')->join('item b','a.item_code=b.code','LEFT')->join('u9_pr c','a.pr_code = c.pr_code','LEFT')->limit("$start,$length")->select();
        if($list){
            $list = collection($list)->toArray();
        }
        //dump($list);
        return $list;
     }

     function getListNum(){
         $count = IoModel::alias('a')->field('a.*,b.desc,c.pro_no,')->join('item b','a.item_code=b.code','LEFT')->join('u9_pr c','a.pr_code = c.pr_code','LEFT')->count();
         return $count;
     }
}