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
       // var_dump($list);
        if(!empty($list)){
            $listInfo = [];
            foreach($list  as $key => $item){
                $listInfo[$item['po_code']]['contract_time'] =  date('Y-m-d',$item['contract_time']);
                $listInfo[$item['po_code']]['arr_goods_num_total']  = empty( $listInfo[$item['po_code']]['arr_goods_num_total'] )?'0': $listInfo[$item['po_code']]['arr_goods_num_total'] ;
                $listInfo[$item['po_code']]['pro_goods_num_total']  = empty( $listInfo[$item['po_code']]['pro_goods_num_total'] )?'0': $listInfo[$item['po_code']]['pro_goods_num_total'] ;
                $listInfo[$item['po_code']]['arr_goods_num_total'] += $item['arv_goods_num'];
                $listInfo[$item['po_code']]['pro_goods_num_total'] += $item['pro_goods_num'];
//                $list[$key]['contract_time'] = date('Y-m-d',$item['contract_time']);
//                $list[$key]['content']='到货数量:'.$item['arr_goods_num'].'---未到货数量:'.$item['pro_goods_num'];
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
                $listInfo[$item['po_code']]['statusinfo'] = $statusinfo;
              //  $list[$key]['statusinfo']=$statusinfo;
            }
        }

      //  var_dump($listInfo);
        $this->assign('list',$listInfo);
        return view();
    }
    public function detail(){
        $pr_code = input('pr_code');
        //$pr_code = '1111222';
        $offerLogic = model('Order','logic');
        $detail = $offerLogic->getOrderDetailInfo($pr_code);
        $codeInfo = $offerLogic->getOrderListOneInfo($pr_code);
        // var_dump($codeInfo);
        $imgInfos = explode(',',$codeInfo[0]['contract']);
        $imgInfos=array_filter($imgInfos);
        $this->assign('imgInfos',$imgInfos);
        $this->assign('list',$detail);
        $this->assign('codeInfo',$codeInfo[0]);
        return view();
    }

    public function cancel(){
        $pr_code = input('pr_code');
        $offerLogic = model('Order','logic');
        $detail = $offerLogic->updateStatus($pr_code);
        if($detail){
            return json(['code'=>2000,'msg'=>'成功','data'=>[]]);
        }else{
            return json(['code'=>4000,'msg'=>'更新失败','data'=>[]]);
        }
    }

    public function updateSupconfirmdate(){
        $pr_code = input('pr_code');
        $supconfirmdate =strtotime(input('supconfirmdate')) ;
        $item_code = input("item_code");
        $offerLogic = model('Order','logic');
        $detail = $offerLogic->updateSupconfirmdate($pr_code,$item_code,$supconfirmdate);
        if($detail){
            return json(['code'=>2000,'msg'=>'成功','data'=>[]]);
        }else{
            return json(['code'=>4000,'msg'=>'更新失败','data'=>[]]);
        }
    }

    public function add(){
        if(request()->isPost()){
            $data=input('param.');
            $pr_code = $data['pr_code'];
            $src = $data['src'];
            $offerLogic = model('Order','logic');
            $result = $offerLogic->updatecontract($pr_code,$src);
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            $pr_code = input('pr_code');
            $this->assign('pr_code',$pr_code);
            return view();
        }

    }
}