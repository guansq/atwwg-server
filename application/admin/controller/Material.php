<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\HttpService;
use service\LogService;
use service\DataService;
use think\Db;

class Material extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '物料管理';

    public function index(){
        //得到全部INFO

        $this->assign('title',$this->title);
        return view();
    }

    /**
     * 得到物料信息
     */

    public function getSupList(){
        $logicSupInfo = Model('Item','logic');
        $list = $logicSupInfo->getListInfo();
        return $list;
    }

    public function del(){

    }

    public function add(){

    }

    public function edit(){
        return view();
    }

    public function updataU9Info(){
     return HttpService::get(getenv('APP_API_HOME').'/u9api/syncItem');
    }

    public function exportExcel(){

    }
}