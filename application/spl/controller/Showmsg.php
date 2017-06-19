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
        //未处理异常订单
        //dump(getReceDateArr(date('m')));
        $this->assign('waitQuoteNum',$waitQuoteNum);
        $this->assign('poItemNum',$poItemNum);
        $this->assign('pastSuppNum',$pastSuppNum);
        $this->assign('initPoNum',$initPoNum);
        $this->assign('atwSureNum',$atwSureNum);
        $this->assign('title',$this->title);
        return view();
    }

    public function del(){

    }

    public function add(){

    }

}