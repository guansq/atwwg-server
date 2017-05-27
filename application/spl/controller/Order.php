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

class Order extends Base{

    public function index(){
       // atw_po_item
        $offerLogic = model('Order','logic');
        $list = $offerLogic->getOrderListInfo();

        foreach($list  as $key => $item){
            $list[$key]['contract_time'] = date('Y-m-d H:i:s',$item['contract_time']);
            $list[$key]['content']='到货数量:'.$item['arr_goods_num'].'---未到货数量:'.$item['pro_goods_num'];
            $statusinfo = '';
            switch ($item['status']){
                case 'init':
                    $statusinfo = '初始';
                    break;
                case 'sup_cancel':
                    $statusinfo = '供应商取消';
                    break;
                case 'sup_edit':
                    $statusinfo = '供应商修改';
                    break;
                case 'atw_sure':
                    $statusinfo = '安特威确定';
                    break;
                case 'sup_sure':
                    $statusinfo = '供应商确定/待上传合同';
                    break;
                case 'upload_contract':
                    $statusinfo = '供应商已经上传合同';
                    break;
                case 'contract_pass':
                    $statusinfo = '合同审核通过';
                    break;
                case 'contract_refuse':
                    $statusinfo = '合同审核拒绝';
                    break;
                case 'executing':
                    $statusinfo = '执行中';
                    break;
                case 'finish':
                    $statusinfo = '结束';
                    break;
                default:
                    $statusinfo = '初始';

            }
            $list[$key]['statusinfo']=$statusinfo;
        }
        //var_dump($list);
        $this->assign('list',$list);
        return view();
    }
    public function detail(){
        return view();
    }
}