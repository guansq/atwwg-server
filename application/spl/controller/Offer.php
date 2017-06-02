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
use PHPExcel_IOFactory;
use PHPExcel;
class Offer extends Base{
    public function index(){
        //获取报价中心状态
        $status = ['init'=>'未报价','quoted'=>'已报价','winbid_uncheck'=>'中标但是需要审核','winbid_checked'=>'中标已经审核',
            'winbid'=>'中标','giveupbid'=>'弃标','close'=>'已关闭'];
        $this->assign('status',$status);
        return view();
    }

    public function getOrderList(){
        $sup_code = session('spl_user')['sup_code'];
        $offerLogic = model('Offer','logic');
        $where = [];
        $data = input('param.');
//        var_dump( $data);
        // 应用搜索条件
        if (!empty($data)){
            foreach (['status'] as $key) {
                if (isset($data[$key]) && $data[$key] !== '' ) {
                    if($key == 'status' && $data[$key] == 'all') {
                           continue;
                    }
                    $where[$key] =  $data[$key];
                }
            }
            if(!empty($data['quote_begintime']) && !empty($data['quote_endtime'])){
                $where['quote_date'] = array('between',array(strtotime($data['quote_begintime']),strtotime($data['quote_endtime'])));
            }elseif(!empty($data['quote_begintime'])){
                $where['quote_date'] = array('egt',strtotime($data['quote_begintime']));
            }elseif(!empty($data['quote_endtime'])){
                $where['quote_date'] = array('elt',strtotime($data['quote_endtime']));
            }
        }
        $list = $offerLogic->getOfferInfo($sup_code,$where);
        //状态init=未报价  quoted=已报价  winbid=中标 giveupbid=弃标  close=已关闭
        foreach($list as $k => $v){
            if(in_array($v['status'],['init'])){
                $list[$k]['showinfo'] = '';
            }else{
                $list[$k]['showinfo'] = 'disabled';
            }
            $list[$k]['promise_date']  =  empty($v['promise_date'])?'':date('Y-m-d',$v['promise_date']);
            $list[$k]['quote_date']  =  empty($v['quote_date'])?'--':date('Y-m-d H:i:s',$v['quote_date']);
            $list[$k]['quote_endtime']  =  empty($v['quote_endtime'])?'--':date('Y-m-d H:i:s',$v['quote_endtime']);
            $list[$k]['req_date']  =  empty($v['req_date'])?'--':date('Y-m-d H:i:s',$v['req_date']);
            $list[$k]['total_price'] = ($v['price_num']*$v['quote_price']);
            $list[$k]['quote_price'] = empty($v['quote_price'])?'':$v['quote_price'];
            $list[$k]['remark'] = empty($v['remark'])?'':$v['remark'];
        }
        //dump($returnInfo);
        $info = ['draw'=>time(),'data'=>$list,'extData'=>[],];
        return json($info);
    }
    //修改包价
    public function savePrice(){
        $data=input('param.');
        $result = $this->validate($data,'Offer');
        if($result !== true){
            return json(['code'=>4000,'msg'=>"$result",'data'=>[]]);
        }
        $offerLogic = model('Offer','logic');
        $key = $data['id'];
        $dataArr = [
            'quote_date'=>time(),
            'promise_date' => strtotime($data['req_date']),
            'quote_price' => $data['quote_price'],
            'remark' => $data['remark'],
            'status' => 'quoted',//改变已报价
        ];
        $list = $offerLogic->updateData($key,$dataArr);
        //dump($list);die;
        if($list !== false){
            $info = $offerLogic->getOneById($key);
            $total_price = ($info['price_num']*$info['quote_price']);
            //dump($offerLogic->toArray());die;
            return json(['code'=>2000,'msg'=>'成功','data'=>['total_price'=>$total_price]]);
        }else{
            return json(['code'=>4000,'msg'=>'更新失败','data'=>[]]);
        }
    }
    //导出表格
    public function exportExcel(){
        $sup_code = session('spl_user')['sup_code'];
        //$path = config('upload_path'); //找到当前脚本所在路径
        //echo $path = dirname(__FILE__);
        //echo die;
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('询价单导出'); //给当前活动sheet设置名称
        $logicSupInfo = Model('Offer','logic');
        $list = $logicSupInfo->getOfferInfo($sup_code,['status'=>'init']);
        $PHPSheet->setCellValue('A1','ID')->setCellValue('B1','物料名称');
        $PHPSheet->setCellValue('C1','采购数量')->setCellValue('D1','交易单位');
        $PHPSheet->setCellValue('E1','计价单位')->setCellValue('F1','询价时间');
        $PHPSheet->setCellValue('G1','报价截止日期')->setCellValue('H1','要求交期');
        $PHPSheet->setCellValue('I1','可供货日期')->setCellValue('J1','单价');
        $PHPSheet->setCellValue('K1','总价')->setCellValue('L1','备注');

        $num = 1;
        foreach($list as $k => $v){
           // var_dump($v);
            $num = $num+1;
            $PHPSheet->setCellValue('A'.$num,$v['id'])->setCellValue('B'.$num,$v['item_name'])
                ->setCellValue('C'.$num,$v['price_num'])->setCellValue('D'.$num,$v['price_uom'])
                ->setCellValue('E'.$num,$v['tc_uom'])->setCellValue('F'.$num,date('Y-m-d H:i:s',$v['quote_date']))
                ->setCellValue('G'.$num,date('Y-m-d H:i:s',$v['quote_endtime']))
                ->setCellValue('H'.$num,date('Y-m-d H:i:s',$v['req_date']))->setCellValue('I'.$num,date('Y-m-d',$v['req_date']))
                ->setCellValue('J'.$num,$v['quote_price'])->setCellValue('K'.$num,($v['price_num']*$v['quote_price']))
                ->setCellValue('L'.$num,$v['remark']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/queryList.xlsx'); //表示在$path路径下面生成itemList.xlsx文件
        $file_name = "queryList.xlsx";
        $contents = file_get_contents($path.'/queryList.xlsx');
        $file_size = filesize($path.'/queryList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

    public function add(){
        return view();
    }

    public function uploadexcel(){
        //$file = request()->file('excel');
        //$info = $file->validate(['size'=>102400,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'upload','');
        $path = input('src');
        //$path = 'http://atwwg.oms.atw.com/static/upload/0386c27f2884e94f/b4ab58206b190d3b.xlsx';
        //dump(parse_url($path));die;
        //$path = ROOT_PATH.'public'.DS.'static'.DS.'upload'.DS.'0863affda05d2d00'.DS.'149660d0799f13c5.xlsx';
        //$path = ROOT_PATH.'public'.DS.'static'.DS.'upload'.DS.'0863affda05d2d00'.DS.'0527123222.xlsx';
        if($path){
            $urlInfo = parse_url($path);
            $pathArr = explode('/',$urlInfo['path']);
            //dump($pathArr);die;
            //$path = ROOT_PATH.'public'.DS.'upload'.DS.$info->getFilename();
            $path = ROOT_PATH.'public'.DS.'static'.DS.'upload'.DS.$pathArr[3].DS.$pathArr[4];
            $logicSupInfo = Model('Offer','logic');
            $fileType=PHPExcel_IOFactory::identify($path);//自动获取文件的类型提供给phpexcel用
            $objReader=PHPExcel_IOFactory::createReader($fileType);//获取文件读取操作对象
            $objReader->setLoadSheetsOnly('询价单导出');//只加载指定的sheet
            $objPHPExcel=$objReader->load($path);//加载文件
            $currentSheet= $objPHPExcel->getSheet(0);
            $allColumn= $currentSheet->getHighestColumn();
            $allRow= $currentSheet->getHighestRow();
            for($currentRow =2;$currentRow <= $allRow;$currentRow++)
            {
                $data = [];
                $data['id'] = intval($objPHPExcel->getActiveSheet()->getCell("A".$currentRow)->getValue());//获取A列的值
                $data['req_date'] = $objPHPExcel->getActiveSheet()->getCell("H".$currentRow)->getValue();//获取H列的值
                $data['quote_price'] = $objPHPExcel->getActiveSheet()->getCell("J".$currentRow)->getValue();//获取J列的值
                $data['remark'] = $objPHPExcel->getActiveSheet()->getCell("L".$currentRow)->getValue();//获取L列的值

                 $offerLogic = model('Offer','logic');
                 $info = $offerLogic->getOneById($data['id']);
                //检查id是否存在
                if(!empty($info) && isset($info['status']) && $info['status']=='init' ){//不存在
                    $key = $data['id'];
                    $dataArr = [
                        'quote_date'=>time(),
                        'promise_date' => strtotime($data['req_date']),
                        'quote_price' => $data['quote_price'],
                        'remark' => $data['remark'],
                    ];
                    $list = $offerLogic->updateData($key,$dataArr);
                }
            }
            $this->success("更新成功！", '');
            //echo $path;
        }else{
            $this->success("上传失败！", '');
        }
    }

}