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

     function getIoInfo($io_code){
         $list = IoModel::alias('a')->field('a.*,b.ctc_name,b.mobile,b.email')
                ->join('supplier_info b','a.sup_code=b.code','LEFT')
                ->where('io_code',"$io_code")->select();
         //->join('u9_pr c','a.pr_code=c.pr_code','LEFT'),c.pro_no
         //echo $this->getLastSql();
         if($list){
             $list = collection($list)->toArray();
         }
         return $list;
     }

     /*
      * 得到询价待审批数量
      */
     function getQuoteNum(){
         return IoModel::where('status','in',['quoted'])->count();
     }

     /*
      *得到流拍询价数量
      */
     function getGiveupNum(){
         return IoModel::where('status','in',['giveupbid'])->count();
     }
}