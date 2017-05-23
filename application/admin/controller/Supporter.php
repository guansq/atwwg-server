<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\DataService;
use service\HttpService;
use think\Db;

class Supporter extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '供应商管理';

    public function index(){
        $this->assign('title',$this->title);

        return view();
    }

    public function showSupporter(){
        $logic = Model('Supporter','logic');
        $list = $logic->getListInfo();
        return $list;
    }

    /**
     * 更新ERP供应商信息到数据库
     */
    public function updataU9Info(){
        return HttpService::curl(getenv('APP_API_HOME').'/u9api/syncSupplier');
    }

    /**
     * 得到供应商信息
     */

    public function getSupList(){
        $logicSupInfo = Model('Supporter','logic');
        $start = input('start');
        $length = input('length');
        $list = $logicSupInfo->getListInfo($start,$length);//分页
        $returnArr = [];
        foreach($list as $k => $v){
            $v['arv_rate'] = $v['arv_rate'] == '' ? '暂无数据' : $v['arv_rate'];
            $v['pp_rate'] = $v['pp_rate'] == '' ? '暂无数据' : $v['pp_rate'];
            $returnArr[] = [
                'code' => $v['code'],
                'name' => $v['name'],
                'type_name' => $v['type_name'],
                'tech_score' => $this->getTechScore(),
                'arv_rate' => $v['arv_rate'],
                'pp_rate' => $v['pp_rate'],
                'quali_score' => $this->getQualiScore(),
                'status' => '正常',
                'pay_type_status' => '正常',
                'quali' => '<a class="edit" href="javascript:void(0);" data-open="'.url('Supporter/edit',['id'=>$v['id']]).'" >查看</a>',
                'action' => '<a class="edit" href="javascript:void(0);" data-open="'.url('Supporter/edit',['id'=>$v['id']]).'" >编辑</a>',
            ];

        }
        $info = ['draw'=>time(),'recordsTotal'=>$logicSupInfo->getListNum(),'recordsFiltered'=>$logicSupInfo->getListNum(),'data'=>$returnArr];

        return json($info);
    }
    public function del(){

    }

    public function add(){

    }

    public function edit(){
        $sup_id = intval(input('param.id'));
        $logicSupInfo = Model('Supporter','logic');
        $sup_info = $logicSupInfo->getOneSupInfo($sup_id);//联合查询得到相关信息
        dump($sup_info);
        if($sup_info){
            $this->assign('sup_info',$sup_info);
            $supQuali = $logicSupInfo->getSupQuali($sup_info['code']);
            dump($supQuali);
        }
        return view();
    }

    /*
     * 得到技术评分
     */
    public function getTechScore(){
        return '80分';
    }

    /*
     * 供应商资质评分
     */
    public function getQualiScore(){
        return '70分';
    }
}