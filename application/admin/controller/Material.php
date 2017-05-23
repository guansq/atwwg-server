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
use PHPExcel_IOFactory;
use PHPExcel;

class Material extends BaseController{
    protected $table = 'SystemItem';
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
     return HttpService::curl(getenv('APP_API_HOME').'/u9api/syncItem');
    }

    public function exportExcel(){
        //$path = config('upload_path'); //找到当前脚本所在路径
        $path = dirname(__FILE__);
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('demo'); //给当前活动sheet设置名称
        $logicSupInfo = Model('Item','logic');
        $list = $logicSupInfo->getListInfo();
        $PHPSheet->setCellValue('A1','ID')->setCellValue('B1','物料编码');
        $PHPSheet->setCellValue('C1','物料名称')->setCellValue('D1','主分类名称');
        $PHPSheet->setCellValue('E1','物料描述')->setCellValue('F1','创建时间');
        $PHPSheet->setCellValue('E1','更新时间');
        //dump($list);die;
        $num = 1;
        foreach($list as $k => $v){
            $num += $num+1;
            $PHPSheet->setCellValue('A'.$num,'ID')->setCellValue('B'.$num,'物料编码')
                    ->setCellValue('C'.$num,'物料名称')->setCellValue('D'.$num,'主分类名称')
                    ->setCellValue('E'.$num,'物料描述')->setCellValue('F'.$num,'创建时间')
                    ->setCellValue('E'.$num,'更新时间');
        }
        //dump($list);die;
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/itemList.xlsx'); //表示在$path路径下面生成itemList.xlsx文件
        $file_name = "itemList.xlsx";
        $contents = file_get_contents($path.'/itemList.xlsx');
        $file_size = filesize($path.'/itemList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }
}