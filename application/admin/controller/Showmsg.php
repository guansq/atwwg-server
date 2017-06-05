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
        $oneDay = 24*60*60;
        $showArr = [];
        //显示最近7天的运营信息
        $defultShow = [strtotime("-1 day"),strtotime("-2 day"),strtotime("-3 day"),strtotime("-4 day"),strtotime("-5 day"),strtotime("-6 day"),strtotime("-7 day")];
        foreach($defultShow as $k => $v){
            $showArr[$k]['date'] = date('Y-m-d',$v);
            //未处理请购单数量
            //已发送询价单数量
            //已发送订单数量
            //处理请购单数量失败数量
            //echo date('Y-m-d',$v).'<br>';
        }
        dump($showArr);
        return view();
    }

    public function del(){

    }

    public function add(){

    }

}