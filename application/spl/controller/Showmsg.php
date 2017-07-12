<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\spl\controller;

use service\LogService;
use service\DataService;
use think\Db;

class Showmsg extends Base{
    protected $title = '消息中心';

    public function index(){
        $current_time = time();
        $quoteLogic = model('Offer','logic');
        $itemLogic = model('Order','logic');
        $suppLogic = model('Supportercenter','logic');

        $sup_code = session('spl_user.sup_code');
        //待报价
        $where = [
            'sup_code' => $sup_code,
            'status' => 'init'
        ];
        $waitQuoteNum = $quoteLogic->getWaitQuoteNum($where);
        //订单逾期警告
        $poItemNum = $itemLogic->getPoItemNum($sup_code);
        //资质过期提醒
        $pastSuppNum = $suppLogic->getPastSuppNum($current_time,$sup_code);
        //新订单
        $where = [
            'sup_code' => $sup_code,
            'status' => 'init'
        ];
        $initPoNum = $itemLogic->getInitPoNum($where);
        //合同未回传
        $where = [
            'sup_code' => $sup_code,
            'status' => 'atw_sure'
        ];
        $atwSureNum = $itemLogic->getInitPoNum($where);

        $this->assign('waitQuoteNum',$waitQuoteNum);
        $this->assign('poItemNum',$poItemNum);
        $this->assign('pastSuppNum',$pastSuppNum);
        $this->assign('initPoNum',$initPoNum);
        $this->assign('atwSureNum',$atwSureNum);
        $this->assign('title',$this->title);
        return view();
    }

    /*
     * 得到交货及时率以及质量合格率
     */
    public function getCharData(){
        $sup_code = session('spl_user.sup_code');
        $receMonthArr = getReceDateArr(date('m'));
        $suppLogic = model('Supportercenter','logic');
        $where = [
            'sup_code' => $sup_code,
        ];
        $monArr = [];
        //$valArr = [];
        $avgArvArr = [];
        $avgPassArr = [];
        foreach($receMonthArr as $k => $v){
            $startTime = strtotime($v);
            $endTime = getEndMonthTime($v);
            $monArr[$k] = date('Y-m',strtotime($v));

            $avgArvVal = keepdecimal($suppLogic->getAvgArvRate($where,$startTime,$endTime));//到达率
            $avgArvVal = initPerVal($avgArvVal, true, false)*1;
            $avgArvArr[$k] = $avgArvVal;

            $avgPassVal = keepdecimal($suppLogic->getAvgPassRate($where,$startTime,$endTime));//合格率
            $avgPassVal = initPerVal($avgPassVal, true, false)*1;//转化百分比
            $avgPassArr[$k] = $avgPassVal;
        }
        if(empty($monArr) || empty($avgArvArr) || empty($avgPassArr)){
            return json(['code' => 6000,'msg' => '抱歉，暂无数据', 'data'=> []]);
        }
        $data = [
            'monthList' => array_reverse($monArr),
            'avgArvList' => array_reverse($avgArvArr),
            'avgPassList' => array_reverse($avgPassArr),
        ];
        return json(['code' => 2000,'msg' => '成功', 'data'=> $data]);
    }

    public function updateLog(){
        return view();
    }

    public function del(){

    }

    public function add(){

    }

}