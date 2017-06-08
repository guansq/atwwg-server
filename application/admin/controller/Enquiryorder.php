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
use PHPExcel_IOFactory;
use PHPExcel;

class Enquiryorder extends BaseController{
    protected $table = 'Io';
    protected $title = '询价单管理';

    public function index(){
        $statusArr=[

        ];
        $this->assign('title',$this->title);
        $this->assign('statusArr',$statusArr);
        return view();
    }

    public function getInquiryList(){
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $logicIoInfo = Model('Io','logic');
        $prLogic = Model('RequireOrder','logic');
        $get = input('param.');
        $where = [];
        if((isset($get['start_time']) && $get['start_time'] !== '') && (isset($get['end_time']) && $get['end_time'] !== '')){
            $get['start_time'] = strtotime($get['start_time']);
            $get['end_time'] = strtotime($get['end_time']);
            $where = [
                'a.create_at' => ['between',[$get['start_time'],$get['end_time']]]
            ];
        }
        if(isset($get['status']) && $get['status'] !== ''){
            $where['pr.status'] = $get['status'];
        }
        $list = $logicIoInfo->getIoList($start,$length,$where);
        $returnArr = [];

        foreach($list as $k => $v){
            //得到全部的询价单 by pr_code item_code
            $where = [
                //'pr_code' => $v['pr_code'],
                //'item_code' => $v['item_code'],
                'pr_id' => $v['pr_id'],
            ];
            $allIo = $logicIoInfo->getIoCountByWhere($where);
            //得到已报价的询价单by pr_code item_code status
            $where = [
                'pr_id' => $v['pr_id'],
                'quote_price' => ['<>',''],//已报价
                'quote_date' => ['<>','']//已报价日期
            ];
            $quotedIo = $logicIoInfo->getIoCountByWhere($where);

            /*if($quotedIo < $allIo){
                $status_desc = '询价中';
            }else{
                $status_desc = '已报价';
            }*/
            $statusArr = [
                'init' => '待询价',
                'hang' => '挂起',
                'inquiry' => '询价中',
                'quoted' => '供应商全部报价完毕',
                'flow' => '流标',
                'winbid' => '已评标',
                'order' => '已下单',
                'close' => '关闭'
            ];
            $prStatus = $prLogic->getPrStatus(['id'=>$v['pr_id']]);
            $status_desc = $statusArr[$prStatus];
            $returnArr[] = [
                'io_code' => $v['io_code'],//询价单号
                'pr_code' => $v['pr_code'],//请购单号
                'item_code' => $v['item_code'],//料号
                'desc' => $v['desc'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => date('Y-m-d',$v['req_date']),//交期
                'quote_date' =>  date('Y-m-d',$v['create_at']),//询价日期
                'quote_endtime' =>  date('Y-m-d',$v['quote_endtime']),//报价截止日期
                'price_status' => $quotedIo.'/'.$allIo,//报价状态
                'status' => $status_desc,//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => '<a class="" href="javascript:void(0);" data-open="/enquiryorder/particulars/io_code/'.$v['io_code'].'">详情</a>',//详情
            ];

        }

        $info = ['draw'=>time(),'recordsTotal'=>$logicIoInfo->getListNum($where),'recordsFiltered'=>$logicIoInfo->getListNum($where),'data'=>$returnArr];

        return json($info);
    }


    public function del(){

    }

    public function add(){

    }

    public function particulars(){
        //列出所有的询价单
        //$commonInfo = ;
        $ioCode = input('param.io_code');
        $logicIoInfo = Model('Io','logic');
        $info = $logicIoInfo->getIoInfo($ioCode);
        $prLogic = Model('RequireOrder','logic');
        $commonInfo = $info[0];//单个记录
        $prId = $commonInfo['pr_id'];

        //得到全部的询价单 by pr_code item_code
        $where = [
            //'pr_code' => $v['pr_code'],
            //'item_code' => $v['item_code'],
            'pr_id' => $prId,
        ];
        $allIo = $logicIoInfo->getIoCountByWhere($where);
        //得到已报价的询价单by pr_code item_code status
        $where = [
            'pr_id' => $prId,
            'quote_price' => ['<>',''],//已报价
            'quote_date' => ['<>','']//已报价日期
        ];
        $quotedIo = $logicIoInfo->getIoCountByWhere($where);

        $statusArr = [
            'init' => '初始',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'flow' => '流标',
            'winbid' => '已评标',
            'order' => '已下单',
            'close' => '关闭'
        ];
        $prStatus = $prLogic->getPrStatus(['id'=>$prId]);
        $status_desc = $statusArr[$prStatus];

        $commonInfo['price_status'] = $quotedIo.'/'.$allIo;//报价状态
        $commonInfo['status_desc'] = $status_desc;//状态
        $this->assign('ioInfo',$info);
        $this->assign('commonInfo',$commonInfo);
        //dump($commonInfo);
        return view();
    }

    /*
     * 立即评标
     */
    public function quickbid(){
        $info = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/evaluateBid'));
        return json($info);
    }

    /*
     * 导出excel
     */
    function exportExcel(){
        $logicIoInfo = Model('Io','logic');
        $prLogic = Model('RequireOrder','logic');
        $get = input('param.');
        $where = [];
        if((isset($get['start_time']) && $get['start_time'] !== '') && (isset($get['end_time']) && $get['end_time'] !== '')){
            $get['start_time'] = strtotime($get['start_time']);
            $get['end_time'] = strtotime($get['end_time']);
            $where = [
                'a.create_at' => ['between',[$get['start_time'],$get['end_time']]]
            ];
        }
        if(isset($get['status']) && $get['status'] !== ''){
            $where['pr.status'] = $get['status'];
        }
        $list = $logicIoInfo->getIoAllList($where);
        $returnArr = [];

        foreach($list as $k => $v){
            //得到全部的询价单 by pr_code item_code
            $where = [
                //'pr_code' => $v['pr_code'],
                //'item_code' => $v['item_code'],
                'pr_id' => $v['pr_id'],
            ];
            $allIo = $logicIoInfo->getIoCountByWhere($where);
            //得到已报价的询价单by pr_code item_code status
            $where = [
                'pr_id' => $v['pr_id'],
                'quote_price' => ['<>',''],//已报价
                'quote_date' => ['<>','']//已报价日期
            ];
            $quotedIo = $logicIoInfo->getIoCountByWhere($where);

            /*if($quotedIo < $allIo){
                $status_desc = '询价中';
            }else{
                $status_desc = '已报价';
            }*/
            $statusArr = [
                'init' => '待询价',
                'hang' => '挂起',
                'inquiry' => '询价中',
                'quoted' => '供应商全部报价完毕',
                'flow' => '流标',
                'winbid' => '已评标',
                'order' => '已下单',
                'close' => '关闭'
            ];
            $prStatus = $prLogic->getPrStatus(['id'=>$v['pr_id']]);
            $status_desc = $statusArr[$prStatus];
            $returnArr[] = [
                'io_code' => $v['io_code'],//询价单号
                'pr_code' => $v['pr_code'],//请购单号
                'item_code' => $v['item_code'],//料号
                'desc' => $v['desc'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => date('Y-m-d',$v['req_date']),//交期
                'quote_date' =>  date('Y-m-d',$v['create_at']),//询价日期
                'quote_endtime' =>  date('Y-m-d',$v['quote_endtime']),//报价截止日期
                'price_status' => $quotedIo.'/'.$allIo,//报价状态
                'status' => $status_desc,//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
            ];

        }

        $list = $returnArr;
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        //dump($list);die;
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('询价单列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1','询价单号');
        $PHPSheet->setCellValue('B1','请购单号');
        $PHPSheet->setCellValue('C1','料号');
        $PHPSheet->setCellValue('D1','交易单位');
        $PHPSheet->setCellValue('E1','计价单位');
        $PHPSheet->setCellValue('F1','数量');
        $PHPSheet->setCellValue('G1','交期');
        $PHPSheet->setCellValue('H1','询价日期');
        $PHPSheet->setCellValue('I1','报价截止日期');
        $PHPSheet->setCellValue('J1','报价状态');
        $PHPSheet->setCellValue('K1','状态');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num+1;
            $PHPSheet->setCellValue('A'.$num,$v['io_code'])->setCellValue('B'.$num,$v['pr_code'])
                ->setCellValue('C'.$num,$v['item_code'])->setCellValue('D'.$num,$v['tc_uom'])
                ->setCellValue('E'.$num,$v['price_uom'])->setCellValue('F'.$num,$v['price_num'])
                ->setCellValue('G'.$num,$v['req_date'])
                ->setCellValue('H'.$num,$v['quote_date'])
                ->setCellValue('I'.$num,$v['quote_endtime'])
                ->setCellValue('J'.$num,$v['price_status'])
                ->setCellValue('K'.$num,$v['status']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/ioList.xlsx'); //表示在$path路径下面生成ioList.xlsx文件
        $file_name = "ioList.xlsx";
        $contents = file_get_contents($path.'/ioList.xlsx');
        $file_size = filesize($path.'/ioList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

}