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
        //->join('u9_pr c','a.pr_code=c.pr_code','LEFT'),c.pro_no
        $list = IoModel::alias('a')
            ->field('a.*,b.desc,pr.pro_no,pr.status as pr_status')
            ->join('item b','a.item_code=b.code','LEFT')
            ->join('u9_pr pr','pr.id = a.pr_id','LEFT')
            ->limit("$start,$length")
            ->group('pr_id')
            ->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
     }

     function getListNum(){
         $count = IoModel::alias('a')->field('a.*,b.desc')->join('item b','a.item_code=b.code','LEFT')->group('pr_code,item_code')->count();
         return $count;
     }

     function getIoCountByWhere($where){
         $count = IoModel::where($where)->count();
         return $count;
     }
}