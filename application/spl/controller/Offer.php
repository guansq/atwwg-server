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
        $this->assign('list',$list);
        dump($list);
        return view();
    }
}