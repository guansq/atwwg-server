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

class Offer extends Base{

    public function index(){
        $sup_code = session('spl_user')['sup_code'];
        $offerLogic = model('Offer','logic');
        $list = $offerLogic->getOfferInfo($sup_code);
        //状态init=未报价  quoted=已报价  winbid=中标 giveupbid=弃标  close=已关闭
        foreach($list as $k => $v){
            if(in_array($v['status'],['quoted','winbid','giveupbid','close'])){
                $list[$k]['showinfo'] = 'disabled';
            }else{
                $list[$k]['showinfo'] = '';
            }
            $list[$k]['total_price'] = $v['price_num']*$v['quote_price'];
        }
        $this->assign('list',$list);
        return view();
    }

    public function savePrice(){

    }
}