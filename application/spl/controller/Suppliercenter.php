<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 14:35
 */
namespace app\spl\controller;

use service\DataService;
use think\Db;
use controller\BasicSpl;

class Suppliercenter extends Base{
    protected $table = 'SystemArea';
    protected $title = '供应商中心';

    public function index(){
        $this->assign('title',$this->title);
//        $sup_code = session('spl_user')['sup_code'];
//        $logicSupInfo = Model('Supportercenter','logic');
//        $sup_info = $logicSupInfo->getOneSupInfo($sup_code);//联合查询得到相关信息
//        dump($sup_info);
//        if($sup_info){
//            $this->assign('sup_info',$sup_info);
//            $supQuali = $logicSupInfo->getSupQuali($sup_info['code']);
//            //dump($supQuali);
//        }
        return view();
    }
}