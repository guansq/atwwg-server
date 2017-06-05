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
use think\Db;

class Showmsg extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '相关信息';

    public function index(){
        $current_time = time();
        //询价待审批
        $ioLogic = model('Io','Logic');
        $quoteNum = $ioLogic->getQuoteNum();
        $this->assign('quoteNum',$quoteNum);

        //订单逾期警告    //dump($quoteNum);
        $poLogic = model('Po','Logic');
        $poItemNum = $poLogic->getPoItemNum();
        $this->assign('poItemNum',$poItemNum);

        //供应商资质过期
        $suppLogic = model('Supporter','Logic');
        $pastSuppNum = $suppLogic->getPastSuppNum($current_time);
        $this->assign('pastSuppNum',$pastSuppNum);
        //流拍询价数量
        $giveupNum = $ioLogic->getGiveupNum();
        $this->assign('giveupNum',$giveupNum);
        //运营情况一览表
        $msgNum = $quoteNum + $poItemNum + $pastSuppNum;
        $this->assign('msgNum',$msgNum);
        $this->assign('title',$this->title);
        return view();
    }

    public function del(){

    }

    public function add(){

    }

}