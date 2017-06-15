<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/18
 * Time: 16:17
 */
namespace app\admin\logic;
use app\common\model\SystemBanner as BannerModel;

class Brand extends BaseLogic{

    public  function addBrandStop($params){
        $bs = $this->findByName($params['name']);
        if(!empty($bs)){
            return resultArray(4000,'该品牌已经存在');
        }

        $now = time();
        $params['create_at']= $now;
        $saveData = [
            'name'=>$params['name'],
            'is_enable'=>1,
            'create_at'=>$now,
            'update_at'=>$now
        ];
        $ret = db('brand_stop')->insert($saveData);
        if($ret){
            return resultArray(2000);
        }
        return resultArray(5010,$ret);

    }

    public function findByName($name){
       return db('brand_stop')->where('name',$name)->find();
    }
}

