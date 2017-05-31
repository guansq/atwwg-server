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

    /*
     * 显示导入层
     */
    public function add(){
        return view();
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
        //var_dump($info);
        return view();
    }
    /*
        * 得到单个物料信息
        */
    public function update(){
        $data=input('param.');
        $logicItemInfo = Model('Item','logic');
        $code = $data['code'];
        $data = array(
           'pur_attr'=> $data['purattr'],
            'future_scale'=> $data['futurescale'],
            'price_weight'=> $data['priceweight'],
            'tech_weight'=> $data['techweight'],
            'business_weight'=> $data['businessweight'],
            'standard_date'=> $data['standarddate'],
            'is_stop'=>$data['inlineRadioOptions'],
        );
        //dump($data);die;
        $info = $logicItemInfo->updateByCode($code,$data);
        if($info !== false){
            $this->success('恭喜，保存成功哦！', '');
        }else{
            $this->error('保存失败，请稍候再试！');
        }
    }

    public function updataU9Info(){//同步
        $itemInfo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/syncItem'));//同步物料
        $supItemInfo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/syncSupItem'));//物料-供应商交叉表
        $supInfo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/syncSupplier'));//同步供应商
        $prInfo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/syncPr'));//请购单pr
        $prToIo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/prToInquiry'));//PR生成IO询价单
        return json([
            'itemInfo' => $itemInfo,
            'supItemInfo' => $supItemInfo,
            'supInfo' => $supInfo,
            'prInfo' => $prInfo,
            'prToIo' => $prToIo,
        ]);
    }

    /*
     * 导出
     */
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

    /*
     * 导入
     */
    public function uploadexcel(){
        //$file = request()->file('excel');
        //$info = $file->validate(['size'=>102400,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'upload','');
        $path = input('src');

        if($path){
            $urlInfo = parse_url($path);
            $pathArr = explode('/',$urlInfo['path']);
            //dump($pathArr);die;
            //$path = ROOT_PATH.'public'.DS.'upload'.DS.$info->getFilename();
            $path = ROOT_PATH.'public'.DS.'static'.DS.'upload'.DS.$pathArr[3].DS.$pathArr[4];
            $logicItemInfo = Model('Item','logic');
            $fileType=PHPExcel_IOFactory::identify($path);//自动获取文件的类型提供给phpexcel用
            $objReader=PHPExcel_IOFactory::createReader($fileType);//获取文件读取操作对象
            $objReader->setLoadSheetsOnly('物料列表');//只加载指定的sheet
            $objPHPExcel=$objReader->load($path);//加载文件
            $currentSheet= $objPHPExcel->getSheet(0);
            $allColumn= $currentSheet->getHighestColumn();
            $allRow= $currentSheet->getHighestRow();
            for($currentRow =2;$currentRow <= $allRow;$currentRow++)
            {
                $data = [];
                $data['id'] = intval($objPHPExcel->getActiveSheet()->getCell("A".$currentRow)->getValue());//获取A列的值
                $data['code'] = $objPHPExcel->getActiveSheet()->getCell("B".$currentRow)->getValue();//获取B列的值
                $dataInfo = [];
                $dataInfo['name'] = intval($objPHPExcel->getActiveSheet()->getCell("C".$currentRow)->getValue());//获取C列的值
                $dataInfo['main_name'] = $objPHPExcel->getActiveSheet()->getCell("D".$currentRow)->getValue();//获取D列的值
                $dataInfo['desc'] = $objPHPExcel->getActiveSheet()->getCell("E".$currentRow)->getValue();//获取E列的值
                $dataInfo['update_at'] = time();//获取G列的值
                //检查code是否存在
                if($logicItemInfo->exist($data)){//不存在
                    $logicItemInfo->saveItem($data,$dataInfo);
                }
            }
            $this->success("更新成功！", '');
            //echo $path;
        }else{
            $this->error("上传失败！", '');
        }
    }

}