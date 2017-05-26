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
        $sup_code = session('spl_user')['sup_code'];
        $offerLogic = model('Offer','logic');
        $quote_begintime = '';
        $quote_endtime ='';
        if (!$this->request->isGet()) {
            $tmparray = [];
            $data=input('param.');
            if(!empty($data['quote_begintime']) && !empty($data['quote_endtime'])){
                session('spl_user.quote_begintime',$data['quote_begintime']);
                $quote_begintime = $data['quote_begintime'];
                session('spl_user.quote_endtime',$data['quote_endtime'])  ;
                $quote_endtime = $data['quote_endtime'];
                $tmparray['quote_date'] = array('between',array(strtotime($data['quote_begintime']),strtotime($data['quote_endtime'])));
            }elseif(!empty($data['quote_begintime'])){
                session('spl_user.quote_begintime',$data['quote_begintime'])  ;
                $quote_begintime = $data['quote_begintime'];
                $tmparray['quote_date'] = array('egt',strtotime($data['quote_begintime']));
            }elseif(!empty($data['quote_endtime'])){
                session('spl_user.quote_endtime',$data['quote_endtime'])  ;
                $quote_endtime = $data['quote_endtime'];
                $tmparray['quote_date'] = array('elt',strtotime($data['quote_endtime']));
            }
            $list = $offerLogic->getOfferInfo($sup_code,$tmparray);
        }else{
            $list = $offerLogic->getOfferInfo($sup_code);
        }
        //状态init=未报价  quoted=已报价  winbid=中标 giveupbid=弃标  close=已关闭
        foreach($list as $k => $v){
            if(in_array($v['status'],['quoted','winbid','giveupbid','close'])){
                $list[$k]['showinfo'] = 'disabled';
            }else{
                $list[$k]['showinfo'] = '';
            }
            $list[$k]['total_price'] = $v['price_num']*$v['quote_price'];
        }
        $this->assign('quote_begintime',$quote_begintime);
        $this->assign('quote_endtime',$quote_endtime);
        $this->assign('list',$list);
        return view();
    }

    public function savePrice(){
        $data=input('param.');
        $result = $this->validate($data,'Offer');
        if($result !== true){
            return json(['code'=>4000,'msg'=>"$result",'data'=>[]]);
        }
        $offerLogic = model('Offer','logic');
        $key = $data['id'];
        $dataArr = [
            'req_date' => strtotime($data['req_date']),
            'quote_price' => $data['quote_price'],
            'remark' => $data['remark'],
            'status' => 'quoted',//改变已报价
        ];
        $list = $offerLogic->updateData($key,$dataArr);
        //dump($list);die;
        if($list !== false){
            $info = $offerLogic->getOneById($key);
            $total_price = $info['price_num']*$info['quote_price'];
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
        $list = $logicSupInfo->getOfferInfo($sup_code);
        $PHPSheet->setCellValue('A1','ID')->setCellValue('B1','物料名称');
        $PHPSheet->setCellValue('C1','采购数量')->setCellValue('D1','交易单位');
        $PHPSheet->setCellValue('E1','计价单位')->setCellValue('F1','询价时间');
        $PHPSheet->setCellValue('G1','报价截止日期')->setCellValue('H1','要求交期');
        $PHPSheet->setCellValue('I1','可供货日期')->setCellValue('J1','单价');
        $PHPSheet->setCellValue('K1','总价')->setCellValue('L1','备注');

        $num = 1;
        foreach($list as $k => $v){
            $num = $num+1;
            $PHPSheet->setCellValue('A'.$num,$v['id'])->setCellValue('B'.$num,$v['item_name'])
                ->setCellValue('C'.$num,$v['price_num'])->setCellValue('D'.$num,$v['price_uom'])
                ->setCellValue('E'.$num,$v['tc_uom'])->setCellValue('F'.$num,date('Y-m-d H:i:s',$v['quote_date']))
                ->setCellValue('G'.$num,date('Y-m-d H:i:s',$v['quote_endtime']))
                ->setCellValue('H'.$num,date('Y-m-d H:i:s',$v['req_date']))->setCellValue('I'.$num,date('Y-m-d H:i:s',$v['req_date']))
                ->setCellValue('J'.$num,$v['quote_price'])->setCellValue('K'.$num,$v['total_price'])
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

}