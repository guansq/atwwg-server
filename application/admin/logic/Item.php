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
     * 得到U9供应商数据分页
     */
    public function getListInfo($start,$length){
        $list = ItemModel::limit("$start,$length")->select();
        if($list) {
            $list = collection($list)->toArray();
        }
        //dump($list);die;
        return $list;
    }

    /*
     * 得到U9全部供应商数据
     */
    public function getAllListInfo(){
        $list = ItemModel::select();
        if($list) {
            $list = collection($list)->toArray();
        }
        //dump($list);die;
        return $list;
    }
    /*
     * 得到U9供应商数据总数
     */
    public function getListNum(){
        $num = model('Item')->count();
        //dump($list);die;
        return $num;
    }

    /**
     * 得到物料关联的供应商
     */
    public function getRelationSup($item_code){
        $list = model('U9SupItem')->where("item_code","$item_code")->select();
        //echo $this->getLastSql();
        if($list){
            //echo '111111';
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /**
     *料号信息
     */
    public function getItemInfo($code){
        $list = ItemModel::where("code","$code")->find();
        if($list) {
            $list = $list->toArray();
        }
        return $list;
    }


    /*
     * 编辑物料详情
     */
    function updateByCode($code,$data){
        return ItemModel::where(['code'=>$code])->update($data);
    }

    /*
     * 更新item
     */
    public function saveItem($where,$data){
        return ItemModel::where($where)->update($data);
    }
}