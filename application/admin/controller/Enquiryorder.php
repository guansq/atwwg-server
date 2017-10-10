<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */

namespace app\admin\controller;

use app\admin\logic\RequireOrder as PRLogic;
use PHPExcel;
use PHPExcel_IOFactory;
use service\HttpService;

class Enquiryorder extends BaseController{
    protected $table = 'Io';
    protected $title = '询价单管理';
    const MSGTITLE   = '新的报价单';
    const MSGCONTENT = '您有新的报价单，请尽快查收';

    public function index(){
        $statusArr = [

        ];
        $logicIoInfo = Model('Io', 'logic');
        $allNums = $logicIoInfo->getListNum();
        $this->assign('allNums', $allNums);
        //echo $allNums;
        $this->assign('title', $this->title);
        $this->assign('statusArr', $statusArr);
        return view();
    }

    public function getInquiryList(){
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $logicIoInfo = Model('Io', 'logic');
        $prLogic = Model('RequireOrder', 'logic');
        $get = input('param.');
        $where = [];
        if((isset($get['start_time']) && $get['start_time'] !== '') && (isset($get['end_time']) && $get['end_time'] !== '')){
            $get['start_time'] = strtotime($get['start_time']);
            $get['end_time'] = strtotime($get['end_time']) + 24*60*60;
            $where = [
                'a.create_at' => ['between', [$get['start_time'], $get['end_time']]]
            ];
        }
        if(isset($get['status']) && $get['status'] !== ''){
            if($get['status'] == 'quoted'){
                $where['pr.status'] = 'quoted';
                $where['pr.inquiry_way'] = 'compete';
            }elseif($get['status'] == 'uncheck'){
                $where['pr.status'] = 'quoted';
                $where['pr.inquiry_way'] = 'exclusive';
            }else{
                $where['pr.status'] = $get['status'];
            }
        }
        if(isset($get['item_code']) && $get['item_code'] !== ''){
            $where['pr.item_code'] = ['like', "%{$get['item_code']}%"];
        }
        if(isset($get['item_name']) && $get['item_name'] !== ''){
            $where['pr.item_name'] = ['like', "%{$get['item_name']}%"];
        }

        if(isset($get['pr_code']) && $get['pr_code'] !== ''){
            $where['pr.pr_code'] = ['like', "%{$get['pr_code']}%"];
        }
        $list = $logicIoInfo->getIoList($start, $length, $where);
        $totalNum = $logicIoInfo->getListNum($where);
        $returnArr = [];
        $statusArr = [
            'init' => '待询价',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'quoted' => '待评标',
            'flow' => '流标',
            'winbid' => '已评标',
            'wait' => '待下单',
            'order' => '已下单',
            'close' => '关闭'
        ];

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
                'quote_price' => ['<>', ''],//已报价
                'quote_date' => ['<>', '']//已报价日期
            ];
            $quotedIo = $logicIoInfo->getIoCountByWhere($where);

            /*if($quotedIo < $allIo){
                $status_desc = '询价中';
            }else{
                $status_desc = '已报价';
            }*/

            $status_desc = key_exists($v['pr_status'], $statusArr) ? $statusArr[$v['pr_status']] : $v['pr_status'];
            if($v['pr_status'] == 'quoted' && $v['inquiry_way'] == 'compete'){
                $status_desc = '待评标';
            }elseif($v['pr_status'] == 'quoted' && $v['inquiry_way'] == 'exclusive'){
                $status_desc = '待审核';
            }

            $returnArr[] = [
                'io_code' => $v['io_code'],
                //询价单号
                'pr_code' => $v['pr_code'],
                //请购单号
                'item_code' => $v['item_code'],
                //料号
                'item_name' => $v['item_name'],
                //物料描述
                'desc' => $v['desc'],
                //物料描述
                'pro_no' => $v['pro_no'],
                //项目号
                'tc_uom' => $v['tc_uom'],
                //交易单位
                'tc_num' => $v['tc_num'],
                //交易数量
                'price_uom' => $v['price_uom'],
                //计价单位
                'price_num' => $v['price_num'],
                //计价数量
                'req_date' => date('Y-m-d', $v['req_date']),
                //交期
                'quote_date' => date('Y-m-d', $v['create_at']),
                //询价日期
                'quote_endtime' => date('Y-m-d', $v['quote_endtime']),
                //报价截止日期
                'price_status' => $quotedIo.'/'.$allIo,
                //报价状态
                'status' => $status_desc,
                //状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => '<a class="" href="javascript:void(0);" data-open="/enquiryorder/particulars/io_code/'.$v['io_code'].'">详情</a>',
                //详情
            ];

        }

        $info = ['draw' => time(), 'recordsTotal' => $totalNum, 'recordsFiltered' => $totalNum, 'data' => $returnArr];

        return json($info);
    }

    /*
     * 发送选中的全部消息
     */
    public function sendAllMsg(){
        $allId = input('param.io_id');
        if($allId == ''){
            return json(['code' => 4000, 'msg' => '请传入询价ID', 'data' => []]);
        }
        $ids = explode('|', $allId);
        $logicIoInfo = Model('Io', 'logic');
        $logicSystemUser = Model('SystemUser', 'logic');
        foreach($ids as $k => $v){
            $where = ['a.id' => $v];
            $info = $logicIoInfo->getSupId($where);//通过IOid获取supid->
            $sendInfo = [
                'email' => '',
                'phone' => '',
                'token' => ''
            ];
            if($info){
                $sendInfo['email'] = $info['email'];
                $sendInfo['phone'] = $info['phone'];
                $where = ['id' => $info['sup_id']];//获取token条件
                $sendInfo['token'] = $logicSystemUser->getPushToken($where);
            }
            saveMsg($info['sup_id'], self::MSGTITLE, self::MSGCONTENT);//发送消息
            if(!empty($sendInfo['email'])){
                $sendData = [
                    'rt_appkey' => 'atw_wg',
                    'fromName' => '安特威物供平台',//发送人名
                    'to' => $sendInfo['email'],
                    'subject' => self::MSGTITLE,
                    'html' => self::MSGCONTENT,
                    'from' => 'tan3250204@sina.com',//平台的邮件头
                ];
                HttpService::curl(getenv('APP_API_MSG').'SendEmail/sendHtml', $sendData);
            }
            if(!empty($sendInfo['phone'])){
                $sendData = [
                    'mobile' => $sendInfo['phone'],
                    'rt_appkey' => 'atw_wg',
                    'text' => self::MSGCONTENT,
                ];
                HttpService::curl(getenv('APP_API_MSG').'SendSms/sendText', $sendData);//sendSms($data)
            }
            //echo $sendInfo['token'];
            if(!empty($sendInfo['token'])){
                $sendData = [
                    "platform" => "all",
                    "rt_appkey" => "atw_wg",
                    "alert" => self::MSGTITLE,
                    "regIds" => $sendInfo['token'],
                    //"platform" => "all",
                    "androidNotification" => [
                        "alert" => self::MSGTITLE,
                        "title" => self::MSGCONTENT,
                        "builder_id" => "builder_id",
                        "priority" => 0,
                        "style" => 0,
                        "alert_type" => -1,
                        "extras" => [
                            "0" => "RuiTu",
                            "key" => "value"
                        ]
                    ]/**/
                ];
                //dump($sendData);
                HttpService::curl(getenv('APP_API_MSG').'push', $sendData);
            }

        }
        return json(['code' => 2000, 'smg' => '发送成功', 'data' => []]);
    }

    public function sendOneMsg(){
        $ioId = input('param.io_id');
        $logicIoInfo = Model('Io', 'logic');
        $logicSystemUser = Model('SystemUser', 'logic');
        $where = ['a.id' => $ioId];
        $info = $logicIoInfo->getSupId($where);//通过IOid获取supid->
        $sendInfo = [
            'email' => '',
            'phone' => '',
            'token' => ''
        ];
        if($info){
            $sendInfo['email'] = $info['email'];
            $sendInfo['phone'] = $info['phone'];
            $where = ['id' => $info['sup_id']];//获取token条件
            $sendInfo['token'] = $logicSystemUser->getPushToken($where);
        }
        saveMsg($info['sup_id'], self::MSGTITLE, self::MSGCONTENT);//发送消息
        if(!empty($sendInfo['email'])){
            $sendData = [
                'rt_appkey' => 'atw_wg',
                'fromName' => '安特威物供平台',//发送人名
                'to' => $sendInfo['email'],
                'subject' => self::MSGTITLE,
                'html' => self::MSGCONTENT,
                'from' => 'tan3250204@sina.com',//平台的邮件头
            ];
            HttpService::curl(getenv('APP_API_MSG').'SendEmail/sendHtml', $sendData);
        }
        if(!empty($sendInfo['phone'])){
            $sendData = [
                'mobile' => $sendInfo['phone'],
                'rt_appkey' => 'atw_wg',
                'text' => self::MSGCONTENT,
            ];
            HttpService::curl(getenv('APP_API_MSG').'SendSms/sendText', $sendData);//sendSms($data)
        }
        //echo $sendInfo['token'];
        if(!empty($sendInfo['token'])){
            $sendData = [
                "platform" => "all",
                "rt_appkey" => "atw_wg",
                "alert" => self::MSGTITLE,
                "regIds" => $sendInfo['token'],
                //"platform" => "all",
                "androidNotification" => [
                    "alert" => self::MSGTITLE,
                    "title" => self::MSGCONTENT,
                    "builder_id" => "builder_id",
                    "priority" => 0,
                    "style" => 0,
                    "alert_type" => -1,
                    "extras" => [
                        "0" => "RuiTu",
                        "key" => "value"
                    ]
                ]
            ];
            HttpService::curl(getenv('APP_API_MSG').'push', $sendData);
        }
        return json(['code' => 2000, 'smg' => '发送成功', 'data' => []]);
    }

    public function particulars(){
        //列出所有的询价单
        //$commonInfo = ;
        $this->assign('title', $this->title);
        $ioCode = input('param.io_code');
        $logicIoInfo = Model('Io', 'logic');
        $info = $logicIoInfo->getIoInfo($ioCode);
        $prLogic = Model('RequireOrder', 'logic');
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
            'quote_price' => ['<>', ''],//已报价
            'quote_date' => ['<>', '']//已报价日期
        ];
        $quotedIo = $logicIoInfo->getIoCountByWhere($where);

        $statusArr = [
            'init' => '初始',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'quoted' => '待评标',
            'flow' => '流标',
            'wait' => '待下单',
            'winbid' => '已评标',
            'order' => '已下单',
            'close' => '关闭'
        ];
        $pr = $prLogic->where('id',$prId)->find();
        $status_desc = $statusArr[$pr['status']];

        if($pr['status'] == 'quoted' && $pr['inquiry_way'] == 'compete'){
            $status_desc = '待评标';
        }elseif($pr['status'] == 'quoted' && $pr['inquiry_way'] == 'exclusive'){
            $status_desc = '待审核';
        }


        $commonInfo['price_status'] = $quotedIo.'/'.$allIo;//报价状态
        $commonInfo['status_desc'] = $status_desc;//状态
        $this->assign('ioInfo', $info);
        $this->assign('commonInfo', $commonInfo);
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

        $logicIoInfo = Model('Io', 'logic');
        $prLogic = Model('RequireOrder', 'logic');
        $get = input('param.');
        $where = [];
        if((isset($get['start_time']) && $get['start_time'] !== '') && (isset($get['end_time']) && $get['end_time'] !== '')){
            $get['start_time'] = strtotime($get['start_time']);
            $get['end_time'] = strtotime($get['end_time']) + 24*60*60;
            $where = [
                'a.create_at' => ['between', [$get['start_time'], $get['end_time']]]
            ];
        }
        if(isset($get['status']) && $get['status'] !== ''){
            if($get['status'] == 'quoted'){
                $where['pr.status'] = 'quoted';
                $where['pr.inquiry_way'] = 'compete';
            }elseif($get['status'] == 'uncheck'){
                $where['pr.status'] = 'quoted';
                $where['pr.inquiry_way'] = 'exclusive';
            }else{
                $where['pr.status'] = $get['status'];
            }
        }
        $list = $logicIoInfo->getIoAllList($where);
        $returnArr = [];
        $statusArr = [
            'init' => '待询价',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'quoted' => '待评标',
            'flow' => '流标',
            'winbid' => '已评标',
            'wait' => '待下单',
            'order' => '已下单',
            'close' => '关闭'
        ];

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
                'quote_price' => ['<>', ''],//已报价
                'quote_date' => ['<>', '']//已报价日期
            ];
            $quotedIo = $logicIoInfo->getIoCountByWhere($where);

            /*if($quotedIo < $allIo){
                $status_desc = '询价中';
            }else{
                $status_desc = '已报价';
            }*/

            $status_desc = key_exists($v['pr_status'], $statusArr) ? $statusArr[$v['pr_status']] : $v['pr_status'];
            if($v['pr_status'] == 'quoted' && $v['inquiry_way'] == 'compete'){
                $status_desc = '待评标';
            }elseif($v['pr_status'] == 'quoted' && $v['inquiry_way'] == 'exclusive'){
                $status_desc = '待审核';
            }
            $returnArr[] = [
                'io_code' => $v['io_code'],//询价单号
                'pr_code' => $v['pr_code'],//请购单号
                'item_code' => $v['item_code'],//料号
                'item_name' => $v['item_name'],//
                'desc' => $v['desc'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => date('Y-m-d', $v['req_date']),//交期
                'quote_date' => date('Y-m-d', $v['create_at']),//询价日期
                'quote_endtime' => date('Y-m-d', $v['quote_endtime']),//报价截止日期
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
        $PHPSheet->setCellValueExplicit('A1', '询价单号');
        $PHPSheet->setCellValueExplicit('B1', '请购单号');
        $PHPSheet->setCellValueExplicit('C1', '料号');
        $PHPSheet->setCellValueExplicit('D1', '物料名称');
        $PHPSheet->setCellValueExplicit('E1', '交易单位');
        $PHPSheet->setCellValueExplicit('F1', '计价单位');
        $PHPSheet->setCellValueExplicit('G1', '数量');
        $PHPSheet->setCellValueExplicit('H1', '交期');
        $PHPSheet->setCellValueExplicit('I1', '询价日期');
        $PHPSheet->setCellValueExplicit('J1', '报价截止日期');
        $PHPSheet->setCellValueExplicit('K1', '报价状态');
        $PHPSheet->setCellValueExplicit('L1', '状态');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num + 1;
            $PHPSheet->setCellValueExplicit('A'.$num, $v['io_code'])
                ->setCellValueExplicit('B'.$num, $v['pr_code'])
                ->setCellValueExplicit('C'.$num, $v['item_code'])
                ->setCellValueExplicit('D'.$num, $v['item_name'])
                ->setCellValueExplicit('E'.$num, $v['tc_uom'])
                ->setCellValueExplicit('F'.$num, $v['price_uom'])
                ->setCellValueExplicit('G'.$num, $v['tc_num'])
                ->setCellValueExplicit('H'.$num, $v['req_date'])
                ->setCellValueExplicit('I'.$num, $v['quote_date'])
                ->setCellValueExplicit('J'.$num, $v['quote_endtime'])
                ->setCellValueExplicit('K'.$num, $v['price_status'])
                ->setCellValueExplicit('L'.$num, $v['status']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
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

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 单一资源审核通过 根据询价 单下采购单
     */
    public function placePurchOrderFromIo(){
        $now = time();
        $io_id = input('param.io_id');
        //通过io_id得到io信息
        $ioLogic = model('Io', 'logic');
        $prLogic = new PRLogic();
        $io = $ioLogic->getIoRecord(['id' => $io_id]);
        /*$poData = [
            'pr_code' => $io['pr_code'],
            'sup_code' => $io['sup_code'],
            'is_include_tax' => 1,      //是否含税
            'status' => 'init',
            'create_at' => $now,
            'update_at' => $now,
        ];
        $poId = $this->insertGetId($poData);
        if(!$poId){
            return resultArray(5000);
        };*/
        $poItemData = [
            'po_id' => null,
            'po_code' => null,
            'item_code' => $io['item_code'],
            'item_name' => $io['item_name'],
            'sup_code' => $io['sup_code'],
            'sup_name' => $io['sup_name'],
            'price_num' => $io['price_num'],
            'price_uom' => $io['price_uom'],
            'tc_num' => $io['tc_num'],
            'tc_uom' => $io['tc_uom'],
            'pr_id' => $io['pr_id'],
            'pr_code' => $io['pr_code'],
            'pr_ln' => $io['pr_ln'],
            'sup_confirm_date' => $io['promise_date'],
            'req_date' => $io['req_date'],
            'price' => $io['quote_price'],
            'tax_price' => round($io['quote_price']*(1 + floatval($io['tax_rate'])), 2),
            'amount' => round($io['quote_price']*(1 /*+ floatval($io['tax_rate'])*/)*$io['tc_num'], 2),
            'tc_uom_code' => $io['tc_uom_code'],          //交易单位编码
            'price_uom_code' =>$io['price_uom_code'],     //计价单位编码
            'tax_rate' => $io['tax_rate'],
            'pro_no' => $prLogic->where('id',$io['pr_id'])->value('pro_no'),
            'pro_goods_num' => $io['tc_num'],
            'winbid_time' => $now,
            'create_at' => $now,
            'update_at' => $now
        ];
        model('Po', 'logic')->savePoItem($poItemData);//保存pi表
        //更改pr表状态为待下单wait
        model('RequireOrder', 'logic')->updatePr(['id' => $io['pr_id']], ['status' => 'wait']);
        //更改io表状态为中标winbid
        model('Io', 'logic')->updateIo(['id' => $io_id], ['status' => 'wait', 'winbid_date' => time(),'refuse_cause'=>'']);
        return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
    }

    public function refuseAndClear(){
        $io_id = input('param.io_id');
        $refuse_cause = input('param.refuse_cause');
        $clearInfo = [
            'promise_date' => '',
            'quote_price' => '',
            'quote_date' => null,
            'remark' => '',
            'winbid_date' => '',
            'refuse_cause' => $refuse_cause,
            'status' => 'init'
        ];
        $io = model('Io', 'logic')->where('id', $io_id)->find();
        if(empty($io)){
            return json(['code' => 4000, 'msg' => '无效的ioId='.$io_id, 'data' => []]);
        }
        //更改io表状态为中标init
        model('Io', 'logic')->updateIo(['id' => $io_id], $clearInfo);
        model('U9Pr')->where('id', $io->pr_id)->update(['status' => 'inquiry']);
        return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
    }

}