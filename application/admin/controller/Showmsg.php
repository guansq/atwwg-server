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
    protected $table = 'AskReply';
    protected $title = '首页';

    public function index(){
        $current_time = time();
        $ioLogic = model('Io','logic');
        $poLogic = model('Po','logic');
        $prLogic = model('RequireOrder','logic');
        //待询价请购单
        $quoteNum = $prLogic->getUnQuoteNum();
        $this->assign('quoteNum',$quoteNum);

        //订单逾期警告    //dump($quoteNum);
        $poItemNum = $poLogic->getPoItemNum();
        $this->assign('poItemNum',$poItemNum);

        //供应商资质过期
        $suppLogic = model('Supporter','logic');
        $pastSuppNum = $suppLogic->countPastSupNum();
        //供应商资质待审核
        $unCheckNum = $suppLogic->countQlfUnchecked();
        $this->assign('pastSuppNum',$pastSuppNum);
        $this->assign('unCheckNum',$unCheckNum);
        //流拍询价数量
        $giveupNum = $ioLogic->getGiveupNum();
        $this->assign('giveupNum',$giveupNum);

        //高信用風險供应商数量
        $creditRiskCount = $suppLogic->countCreditRisk();
        $this->assign('creditRiskCount',$creditRiskCount);
        //运营情况一览表
        $messLogic = model('AskReply','logic');
        $msgNum = $messLogic->getAskUnreadNum();
        $this->assign('msgNum',$msgNum);
        $this->assign('title',$this->title);
        $prLogic = model('RequireOrder', 'logic');
        $oneDay = 24*60*60;
        $showArr = [];
        $revData = input('param.');
        //dump($revData);
        //echo url('enquiryorder/index',array('status'=>'init'));
        //显示最近7天的运营信息
        $defultShow = [strtotime("-1 day"),strtotime("-2 day"),strtotime("-3 day"),strtotime("-4 day"),strtotime("-5 day"),strtotime("-6 day"),strtotime("-7 day")];
        if(isset($revData['startTime']) && isset($revData['endTime'])){
            //echo strtotime($revData['endTime']);
            $endTime = strtotime($revData['endTime']) > time() ? time() : strtotime($revData['endTime']);
            $midtime = $endTime - strtotime($revData['startTime']);
            if($midtime >= $oneDay){
                $days = $midtime/$oneDay;
                $defultShow = [];

                for ($x=0; $x<=$days; $x++) {
                    $endTime = $defultShow[$x] = $endTime - $oneDay;
                    //echo date('y-m-d',$endTime);
                }
                //echo $days;
            }
        }
//        dump($defultShow);
//        die;
        foreach($defultShow as $k => $v){
            $showArr[$k]['date'] = date('Y-m-d',$v);
            $startTime = $v;
            $endTime = $v+$oneDay;
            //未处理请购单数量 init
            $where = [
                'update_at'=>['between',"$startTime,$endTime"],
                'status'=>'hang'//init
            ];

            $showArr[$k]['unfinishPrnum'] = $prLogic->getNumByWhere($where);
            //已发送询价单数量
            $where = [];
            $where = [
                'update_at'=>['between',"$startTime,$endTime"],
            ];
            $showArr[$k]['ioNum'] = $ioLogic->getIoNumByWhere($where);
            //已发送订单数量
            $where = [];
            $where = [
                'update_at'=>['between',"$startTime,$endTime"],
            ];
            $showArr[$k]['poNum'] = $poLogic->getPoNumByWhere($where);
            //处理请购单数量失败数量 hang=挂起 close = 关闭
            $where = [];
            $where = [
                'update_at'=>['between',"$startTime,$endTime"],
                'status'=>['in',['close']]//$v['pur_attr']
            ];
            $showArr[$k]['failPrNum'] = $prLogic->getNumByWhere($where);
            //echo date('Y-m-d',$v).'<br>';
        }
        //dump($showArr);
        $this->assign('showInfo',$showArr);
        return view();
    }

    public function updateLog(){
        return view();
    }


    public function del(){

    }

    public function add(){

    }

    public function runInfo(){

    }

}