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
    public function getOrderList(){
        $offerLogic = model('Order','logic');
        $sup_code = session('spl_user')['sup_code'];
        $where = ['sup_code'=>$sup_code];
        $data = input('param.');
//        var_dump( $data);
        // 应用搜索条件
        if (!empty($data)){
            foreach (['status'] as $key) {
                if (isset($data[$key]) && $data[$key] !== '' ) {
                    if($key == 'status' && $data[$key] == 'all') {
                        continue;
                    }
                    $where[$key] =  $data[$key];
                }
            }
            if(!empty($data['quote_begintime']) && !empty($data['quote_endtime'])){
                $where['create_at'] = array('between',array(strtotime($data['quote_begintime']),strtotime($data['quote_endtime'])));
            }elseif(!empty($data['quote_begintime'])){
                $where['create_at'] = array('egt',strtotime($data['quote_begintime']));
            }elseif(!empty($data['quote_endtime'])){
                $where['create_at'] = array('elt',strtotime($data['quote_endtime']));
            }
        }
        $list = $offerLogic->getPolist($where);
        $returnInfo = [];
        $status = [
            '' => '',
            'init' => '待签订',
            'sup_cancel' => '供应商取消',
            'sup_edit' => '供应商修改',
            'atw_sure' => '安特威确定',
            'sup_sure' => '供应商确定/待上传合同',
            'upload_contract' => '供应商已经上传合同',
            'contract_pass' => '合同审核通过',
            'contract_refuse' => '合同审核拒绝',
            'executing' => '执行中',
            'finish' => '结束',
        ];
       // var_dump($list);
        foreach($list as $k => $v){
            $returnInfo[$k]['checked'] = $v['id'];
            $exec_desc = '';
            if(!empty($itemInfo = $offerLogic->getPoItemInfo($v['id']))){
                foreach($itemInfo as $vv){
                    $vv['arv_goods_num'] = $vv['arv_goods_num'] == '' ? 0 : $vv['arv_goods_num'];
                    $vv['pro_goods_num'] = $vv['pro_goods_num'] == '' ? 0 : $vv['pro_goods_num'];
                    $exec_desc .= '物料名称：'.$vv['item_name'].'; '.'到货数量：'.$vv['arv_goods_num'].'; 未到货数量：'.$vv['pro_goods_num'].'<br>';
                }
                $returnInfo[$k]['exec_desc'] = $exec_desc;
            }else{
                $returnInfo[$k]['exec_desc'] = '';
            }
            $returnInfo[$k]['order_code'] = $v['order_code'];
            $returnInfo[$k]['pr_code'] = $v['pr_code'];
          //  $returnInfo[$k]['pr_date'] = date('Y-m-d',$offerLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['create_at'] = date('Y-m-d',$v['create_at']);
            $returnInfo[$k]['status'] = $status[$v['status']];
            $returnInfo[$k]['contract_time'] = empty($v['contract_time']) ?'--':date('Y-m-d',$v['contract_time']);
            $returnInfo[$k]['detail'] = $v['id'];
        }
        //dump($returnInfo);
        $info = ['draw'=>time(),'data'=>$returnInfo,'extData'=>[],];
        return json($info);
    }
    public function index(){
        $orderStatus = array('init'=>'待签订','sup_cancel'=>'供应商取消',
            'sup_edit'=>'供应商修改','atw_sure'=>'安特威确定',
            'sup_sure'=>'供应商确定/待上传合同','upload_contract'=>'供应商已经上传合同',
            'contract_pass'=>'合同审核通过','contract_refuse'=>'合同审核拒绝',
            'executing'=>'执行中','finish'=> '结束'
        );
        $this->assign('orderstatus',$orderStatus);
        return view();
    }
    public function detail(){
        $pr_code = input('pr_code');
        //$pr_code = '1111222';
        $offerLogic = model('Order','logic');
        $detail = $offerLogic->getOrderDetailInfo($pr_code);
        foreach ($detail as $key => $item){
            $result = $offerLogic->getOrderRecordInfo($pr_code,$item['item_code']);
            $detail[$key]['times'] = (!empty($result)?count($result):0);
        }
        $codeInfo = $offerLogic->getOrderListOneInfo($pr_code);
        $contractable = in_array($codeInfo[0]['status'],array('sup_sure','upload_contract'))?'1':'0';
        $cancelable = in_array($codeInfo[0]['status'],array('init','atw_sure'))?'1':'0';
        $confirmorderable = in_array($codeInfo[0]['status'],array('init','atw_sure'))?'1':'0';
        $confirmable = in_array($codeInfo[0]['status'],array('init','sup_edit','atw_sure'))?'1':'0';
        $statusButton = array('contractable'=>$contractable,'cancelable'=>$cancelable,
            'confirmable'=>$confirmable,'confirmorderable'=>$confirmorderable);
        $imgInfos = explode(',',$codeInfo[0]['contract']);
        $imgInfos=array_filter($imgInfos);
        $this->assign('statusButton',$statusButton);
        $this->assign('imgInfos',$imgInfos);
        $this->assign('list',$detail);
        $this->assign('codeInfo',$codeInfo[0]);
        return view();
    }

    public function cancel(){
        $pr_code = input('pr_code');
        $offerLogic = model('Order','logic');
        $detail = $offerLogic->updateStatus($pr_code,'sup_cancel');
        if($detail){
            return json(['code'=>2000,'msg'=>'成功','data'=>[]]);
        }else{
            return json(['code'=>4000,'msg'=>'更新失败','data'=>[]]);
        }
    }
    public function orderconfirm(){
        $pr_code = input('pr_code');
        $offerLogic = model('Order','logic');
        $detail = $offerLogic->updateStatus($pr_code,'sup_sure');
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
        $detailInfo = $offerLogic->getOrderDetailInfo($pr_code,$item_code);
       // var_dump($detailInfo);
        if(!empty($detailInfo)){
            if (DataService::save('po_record', [ 'po_code' =>$pr_code,'item_code'=>$item_code,'create_at'=>time(),'update_at'=>time(),'po_ln'=>$detailInfo[0]['po_ln'],'promise_date'=>$detailInfo[0]['sup_confirm_date']])) {
                $detail = $offerLogic->updateSupconfirmdate($pr_code,$item_code,$supconfirmdate);
                $detailPo = $offerLogic->updateStatus($pr_code,'sup_edit');
                if($detail){
                    return json(['code'=>2000,'msg'=>'成功','data'=>[]]);
                }else{
                    return json(['code'=>4000,'msg'=>'更新失败','data'=>[]]);
                }
            }else{
                return json(['code'=>4000,'msg'=>'更新失败','data'=>[]]);
            }
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
            $result = $offerLogic->updatecontract($pr_code,$src,'upload_contract');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            $pr_code = input('pr_code');
            $this->assign('pr_code',$pr_code);
            return view();
        }

    }
}