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
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $logicItemInfo = Model('Item','logic');
        $list = $logicItemInfo->getListInfo($start,$length);
        $returnArr = [];
        foreach($list as $k => $v){
            //$v['arv_rate'] = $v['arv_rate'] == '' ? '暂无数据' : $v['arv_rate'];
            //$v['pp_rate'] = $v['pp_rate'] == '' ? '暂无数据' : $v['pp_rate'];
            $returnArr[] = [
                'main_name' => $v['main_name'],//主分类
                'code' => $v['code'],//料号
                'desc' => $v['desc'],//物料描述
                'pur_attr' => $v['pur_attr'],//物料采购属性
                'future_scale' => $v['future_scale'],//货期让步比例
                'price_weight' => $v['price_weight'],//价格权重
                'tech_weight' => $v['tech_weight'],//技术权重
                'business_weight' => $v['business_weight'],//商务权重
                'pay_type_status' => '',//查看\打印条形码<a class="barcode" href="#">条形码</a>
                'action' => '<a class="edit" href="javascript:void(0);" data-open="'.url('Material/edit',['code'=>$v['code']]).'" >编辑</a>',
            ];

        }
        $info = ['draw'=>time(),'recordsTotal'=>$logicItemInfo->getListNum(),'recordsFiltered'=>$logicItemInfo->getListNum(),'data'=>$returnArr];

        return json($info);
        //dump($list);
        //return $list;
    }

    public function del(){

    }

    public function add(){

    }

    /*
     * 得到单个物料信息
     */
    public function edit(){
        $code = input('code');
        $logicItemInfo = Model('Item','logic');
        $info = $logicItemInfo->getItemInfo($code);
        $this->assign('itemInfo',$info);
       // var_dump($info);
        //关联供应商
        $supInfo = $logicItemInfo->getRelationSup($code);
        $this->assign('supInfo',$supInfo);//
        //var_dump($supInfo);
        return view();
    }
    /*
        * 得到单个物料信息
        */
    public function update(){
        $data=input('param.');
        $logicItemInfo = Model('Item','logic');
        $code = $data['code'];
        $where = array(
           'pur_attr'=> $data['purattr'],
            'future_scale'=> $data['futurescale'],
            'price_weight'=> $data['priceweight'],
            'tech_weight'=> $data['techweight'],
            'business_weight'=> $data['businessweight'],
            'standard_date'=> $data['standarddate'],
            'is_stop'=>$data['inlineRadioOptions'],
        );
        $info = $logicItemInfo->updateByCode($code,$where);
        return $info;
    }

    public function updataU9Info(){
     return HttpService::curl(getenv('APP_API_HOME').'/u9api/syncItem');
    }

    public function exportExcel(){
        //$path = config('upload_path'); //找到当前脚本所在路径
        //echo $path = dirname(__FILE__);
        //echo die;
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('物料列表'); //给当前活动sheet设置名称
        $logicItemInfo = Model('Item','logic');
        $list = $logicItemInfo->getAllListInfo();
        $PHPSheet->setCellValue('A1','ID')->setCellValue('B1','物料编码');
        $PHPSheet->setCellValue('C1','物料名称')->setCellValue('D1','主分类名称');
        $PHPSheet->setCellValue('E1','物料描述')->setCellValue('F1','创建时间');
        $PHPSheet->setCellValue('G1','更新时间');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num+1;
            $PHPSheet->setCellValue('A'.$num,$v['id'])->setCellValue('B'.$num,$v['code'])
                    ->setCellValue('C'.$num,$v['name'])->setCellValue('D'.$num,$v['main_name'])
                    ->setCellValue('E'.$num,$v['desc'])->setCellValue('F'.$num,date('Y-m-d H:i:s',$v['create_at']))
                    ->setCellValue('G'.$num,date('Y-m-d H:i:s',$v['update_at']));
        }
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