<?php
namespace app\index\controller;

class Index
{
    //请购单 列表
    private $mapArr = [
        'ApprovedBy' => 'approved_by',
        'ApprovedDate' => 'approved_date',
        'DeliveryDate' => 'delivery_date',
        'DocDate' => 'doc_date',
        'ItemCode' => 'item_code',
        'ItemID' => 'item_id',
        'ItemName' => 'item_name',
        'PRID' => 'pr_id',
        'PRLineID' => 'pr_line_id',
        'PRLineNo' => 'pr_line_no',
        'PRNo' => 'pr_no',
        'ReqQty' => 'req_qty',
        'TradeUOMCode' => 'trade_uom_code',
        'TradeUOMID' => 'trade_uom_id',
        'TradeUOMName' => 'trade_uom_name',
    ];
    public $apiurl_approve = "http://u9.ruitukeji.cc:8081/U9POPR/UFIDA.U9.ZHC.PR.svc?wsdl";//请购单 列表
    public $apiurl_sup_item = "http://u9.ruitukeji.cc:8081/U9POPR/UFIDA.U9.ZHC.IItem.SupItem.svc?wsdl"; //料品+供应商交叉表
    public $apiurl_supper = "http://u9.ruitukeji.cc:8081/U9POPR/UFIDA.U9.ZHC.ISupplier.Supplier.svc?wsdl";//供应商
    public $apiurl_item = "http://u9.ruitukeji.cc:8081/U9POPR/UFIDA.U9.ZHC.IItem.Item.svc?wsdl";//料品

    public function index(){
        echo 'test';
    }
    //请购单 列表
    public function approve()
    {
        //require_once VENDOR_PATH.'nusoap/nusoap.php';
        //Vendor('nusoap.nusoap');
        $client = new \SoapClient($this->apiurl_approve);
        //获取SoapClient对象引用的服务所提供的所有方法
        $result = $client->FindByApproveDate()->FindByApproveDateResult->PRApprovedEntity;
        $temp_arr = [];
        foreach($result as $k => $v){
            $temp_arr[$k]['approved_by'] = $v->ApprovedBy;
            $temp_arr[$k]['approved_date'] = $v->ApprovedDate;
            $temp_arr[$k]['delivery_date'] = $v->DeliveryDate;
            $temp_arr[$k]['doc_date'] = $v->DocDate;
            $temp_arr[$k]['item_code'] = $v->ItemCode;
            $temp_arr[$k]['item_id'] = number_format($v->ItemID,0,'','');
            $temp_arr[$k]['item_name'] = $v->ItemName;
            $temp_arr[$k]['pr_id'] = number_format($v->PRID,0,'','');
            $temp_arr[$k]['pr_line_id'] = number_format($v->PRLineID,0,'','');
            $temp_arr[$k]['pr_line_no'] = $v->PRLineNo;
            $temp_arr[$k]['pr_no'] = $v->PRNo;
            $temp_arr[$k]['req_qty'] = $v->ReqQty;
            $temp_arr[$k]['trade_uom_code'] = $v->TradeUOMCode;
            $temp_arr[$k]['trade_uom_id'] = number_format($v->TradeUOMID,0,'','');
            $temp_arr[$k]['trade_uom_name'] = $v->TradeUOMName;
        }
        //dump($temp_arr);
        $model = model("approve_list");
        //执行批量更新操作
        foreach($temp_arr as $k => $v){
            $temp_line_id = $v['pr_line_id'];
            $ishave = $model->where('pr_line_id',$temp_line_id)->count('pr_line_id');
            if($ishave === 0){
                $model->insert($v);//添加
                echo $model->getLastSql().'<br>';
            }else{
                $model->where('pr_line_id',$temp_line_id)->update($v);
            }
        }
        echo '更新成功！';
    }

    //料品+供应商交叉表
    public function sup_item(){
        $client = new \SoapClient($this->apiurl_sup_item);
        $result = $client->FindByApproveDate()->FindByApproveDateResult->PRApprovedEntity;
        $temp_arr = [];
        foreach($result as $k => $v){
            $temp_arr[$k]['item_code'] = $v->ItemCode;
            $temp_arr[$k]['item_name'] = $v->ItemName;
            $temp_arr[$k]['sup_code'] = $v->SupCode;
            $temp_arr[$k]['sup_name'] = $v->SupName;
        }
        //dump($temp_arr);
        $model = model('sup_item');
        foreach($temp_arr as $k => $v){
            $data = [
                'item_code' => $v['item_code'],
                'item_name' => $v['item_name'],
                'sup_code' => $v['sup_code'],
                'sup_name' => $v['sup_name'],
            ];
            $item_id = $model->field('id')->where($data)->value('id');
            //dump($item_id['id']);
            if(empty($item_id)){
                $model->insert($v);//添加
                //echo $model->getLastSql().'<br>';
            }else{
                $model->where('id',$item_id)->update($data);
            }
        }
        echo '更新成功！';
    }

    //供应商
    public function supplier(){
        $client = new \SoapClient($this->apiurl_supper);
        $result = $client->FindByApproveDate()->FindByApproveDateResult->KPRApprovedEntity;
        $temp_arr = [];
        foreach($result as $k => $v){
            $temp_arr[$k]['sup_code'] = $v->SupCode;
            $temp_arr[$k]['sup_name'] = $v->SupName;
        }
        //dump($temp_arr);die;
        $model = model('supplier');
        foreach($temp_arr as $k => $v){
            $data = [
                'sup_code' => $v['sup_code'],
                'sup_name' => $v['sup_name'],
            ];
            $item_id = $model->field('id')->where($data)->value('id');
            if(empty($item_id)){
                $model->insert($v);//添加
                //echo $model->getLastSql().'<br>';
            }else{
                $model->where('id',$item_id)->update($data);
            }
        }
        echo '更新成功！';
    }

    //料品
    public function item(){
        $client = new \SoapClient($this->apiurl_item);
        $result = $client->FindByApproveDate()->FindByApproveDateResult->ItemApprovedEntity;
        $temp_arr = [];
        foreach($result as $k => $v){
            $temp_arr[$k]['item_code'] = $v->ItemCode;
            $temp_arr[$k]['item_name'] = $v->ItemName;
        }
        $model = model('item');
        //dump($temp_arr);die;
        foreach($temp_arr as $k => $v){
            $data = [
                'item_code' => $v['item_code'],
                'item_name' => $v['item_name'],
            ];
            $item_id = $model->field('id')->where($data)->value('id');
            if(empty($item_id)){
                $model->insert($v);//添加
                //echo $model->getLastSql().'<br>';
            }else{
                $model->where('id',$item_id)->update($data);
            }
        }
        echo '更新成功！';
    }
}
