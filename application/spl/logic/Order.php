<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;

use app\common\model\Po;
use app\common\model\PoItem;
use app\common\model\PoRecord;
use service\HttpService;
use TCPDF;

class Order extends BaseLogic{

    protected $table = 'atw_po';

    /*
     * 得到订单列表
     */
    function getPolist($where, $tag = ''){
        $now = time();
        if(empty($where)){
            $list = Po::order('update_at DESC')->select();
        }else{
            $list = Po::where($where)->order('update_at DESC')->select();
        }

        if($tag != 'exceed'){
            return $list;
        }

        $retList = [];
        foreach($list as $po){
            $hasExceed = false;
            $piList = $this->getPoItemInfo($po['id']);
            foreach($piList as $pi){
                $hasExceed = $hasExceed || ($pi['sup_confirm_date'] < $now) && ($pi['pro_goods_num'] > 0) && !in_array($po['status'], [
                        'finish',
                        'sup_cancel'
                    ]);
            }
            if($hasExceed){
                $retList[] = $po;
            }
        }
        return $retList;
    }

    /*
     * 得到单个列表信息
     */
    function getPoInfo($id){
        $info = Po::where('id', $id)->find();
        if($info){
            $info = $info->toArray();
        }
        return $info;
    }

    /*
    * 得到订单下的item列表
    */
    function getPoItemInfo($po_id){
        $list = PoItem::where('po_id', $po_id)->select();
        return $list;
    }
    //获取订单中心列表

    /*function getOrderListInfo($sup_code=''){
        $list = Po::alias('po')->field('pi.po_code,po.order_code,po.status,pi.arv_goods_num,pi.pro_goods_num,po.contract_time,pi.item_code')->join('po_item pi','po.id = pi.po_id')->where(['sup_code'=>$sup_code])->order('po.create_at desc')->select();
        //echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }*/
    //获取某条订单状态
    function getOrderListOneInfo($id){
        $list = Po::where(['id' => $id])->select();
        return $list;
    }

    //修改订单状态
    function updateStatus($id, $status = 'sup_cancel'){
        $list = Po::where(['id' => $id])->update(['status' => $status]);
        return $list;
    }

    //更新交期时间
    function updateSupconfirmdate($id, $supconfirmdate){
        $list = PoItem::where(['id' => $id])->update([
            'update_at' => time(),
            'sup_update_date' => $supconfirmdate
            //'status' => 'uncheck'//改成未审核
        ]);

        return $list;
    }

    //调用u9 接口 更新交期时间
    function updateU9Supconfirmdate($pi, $supconfirmdate){
        if(empty($pi['po_ln']) || empty($supconfirmdate)){
            return resultArray(4001, '订单行号和交期不能为空。');
        }
        $reqData = [
            'DocNo' => $pi['po_code'],
            'DocLineNo' => $pi['po_ln'],
            'DeliveryDate' => $supconfirmdate,
        ];
        $httpRet = HttpService::post(getenv('APP_API_U9').'/index/updatePODeliveryDate', $reqData);
        return json_decode($httpRet, true);

    }

    //更新合同图片
    function updatecontract($id, $src, $status){
        $list = Po::where(['id' => $id])->update(['status' => $status, 'contract' => $src]);
        return $list;
    }

    //获取订单详情
    function getOrderDetailInfo($po_id, $item_code = ''){
        if(!empty($po_id)){
            if(empty($item_code)){
                $list = PoItem::where(['po_id' => $po_id])->field('*, "" AS pro_no ')->select();
            }else{
                $list = PoItem::where(['po_id' => $po_id, 'item_code' => $item_code])
                    ->field('*, "" AS pro_no ')
                    ->select();
            }
            foreach($list as &$pi){
                $pi['pro_no'] = model('PR', 'logic')->where('id', $pi->pr_id)->value('pro_no');
            }
            return $list;
        }
        return false;
    }

    //获取订单记录
    function getOrderRecordInfo($id){
        if(!empty($id)){
            $list = PoRecord::where(['pi_id' => $id])->select();
            //echo $this->getLastSql();//die;
            if($list){
                $list = collection($list)->toArray();
                return $list;
            }
        }
        // var_dump($list);
        return false;
    }

    /*
     * 得到即将过期的订单数量
     */
    function getPoItemNum($sup_code){
        return PoItem::alias('pi')
            ->join('po po', 'pi.po_id = po.id')
            ->where('po.status', 'NOT IN', [
                'finish',
                'sup_cancel'
            ])
            ->where('pro_goods_num', '>', 0)
            ->where('pi.sup_code', $sup_code)
            ->where('pi.sup_confirm_date', '<', time())
            ->group('po.id')
            ->count();//得到执行中的订单，和订单未到货数量>0
    }

    /*
     * 得到新订单的数量
     */
    function getInitPoNum($where){
        return Po::where($where)->count();
    }


    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 生产PDF
     * @param $po
     */
    public function downContract($po){

        $supInfo = model('SupplierInfo')->findByCode($po['sup_code']);
        if(empty($supInfo)){
            return $this->error('无效的sup_code='.$po['sup_code']);
        }
        $piList = model('PoItem', 'logic')->getListByPoId($po['id']);
        if(count($piList) == 0){
            return $this->error('没有PI,po.id='.$po['id']);
        }

        $orgName = getSysconf('org_name', '苏州安特威阀门有限公司');
        $orgAddress = getSysconf('org_address', '苏州吴江汾湖开发区越秀路988号');
        $orgTel = getSysconf('org_tel', '0512-82880588');
        $orgFax = getSysconf('org_fax', '0512-82079059');
        $orgBankDeposit = getSysconf('org_bank_deposit', '中国农业银行吴江汾湖支行');
        $orgBankAccount = getSysconf('org_bank_account', '10543701040015106');
        $orgTaxNo = getSysconf('org_tax_no', '913205096829757874');
        $orgReceiveAddress = getSysconf('org_receive_address', '苏州吴江汾湖开发区越秀路988号');
        $orgReceiver = getSysconf('org_receiver', '沈斌');
        $orgReceiverMobile = getSysconf('org_receiver_mobile', '13962546667');
        $today = date('Y-m-d', time());

        //dd($supInfo->toJson());
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('wxx@ruitukeji.com');
        $pdf->SetTitle('安特威采购合同');
        $pdf->SetSubject('安特威采购合同');
        $pdf->SetKeywords('安特威,采购合同');


        // set header and footer fonts
        $fontFamly = 'cid0cs';
        $pdf->setHeaderFont([$fontFamly, '', 5]);
        $pdf->setFooterFont([$fontFamly, '', 6]);

        $pdf->SetMargins(10, 10, 10);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        $pdf->setfont($fontFamly);
        // Add a page
        $pdf->AddPage();

        $html = <<<EOD
<!DOCTYPE html>
<html> 
<style type="text/css">
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed,
figure, figcaption, footer, header, hgroup,
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
    margin: 0;
    padding: 0;
    border: none;
    vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure,
footer, header, hgroup, menu, nav, section {
    display: block;
}
body {
    line-height: 1;
}
ol, ul , li {
    list-style: none;
    list-style-type:none;
}
blockquote, q {
    quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
    content: '';
    content: none;
}
table {
    border-collapse: collapse;
    border-spacing: 0;
}
tr, th, td{
  border: 1px solid #000000;
}

tr>th{
  text-align: center;
}

.border-none tr, .border-none th,.border-none td{
  border: none;
}

.content-center{
  text-align: center;
}
.content-left{
  text-align: left;
}
.content-right{
  text-align: right;
}

.text-small{
  font-size: 0.8em;
}

.agreement-form div , .agreement-form ul{
}

.agreement-form .title{
    font-size: 1em;
    font-weight: bold;
    text-align: center;
    line-height: 1em;
}
.agreement-form .order-code{
  text-align: right;
}

.agreement-form .bg-img{
   background: url("https://www.baidu.com/img/bd_logo1.png");
}
.agreement-form .top{
}
</style>
</head>

<body>
  <div class="agreement-form">  
    <div class="title">采购订单</div>
      <p class="order-code">订单编号：{$po['order_code']}</p>
      <div class="top">
        <table class="border-none" width="100%">
          <tr >
            <td width="12%">买 方：</td>
            <td width="38%">$orgName</td>
            <td width="12%">卖 方：</td>
            <td width="38%">$supInfo[name]</td>
          </tr>
          <tr >
            <td >地 址：</td>
            <td >$orgAddress</td>
            <td >地 址：</td>
            <td >$supInfo[address]</td>
          </tr>
          <tr >
            <td>电 话：</td>
            <td>$orgTel</td>
            <td>电 话：</td>
            <td>$supInfo[mobile]</td>
          </tr>
          <tr>
            <td>传 真：</td>
            <td>$orgFax</td>
            <td>传 真：</td>
            <td>$supInfo[fax]</td>
          </tr>
          <tr>
            <td>开户银行：</td>
            <td>$orgBankDeposit</td>
            <td>开户银行：</td>
            <td>$supInfo[bank_name]</td>
          </tr>
          <tr>
            <td>账 号：</td>
            <td>$orgBankAccount</td>
            <td>账 号：</td>
            <td>$supInfo[bank_account_code]</td>
          </tr>
          <tr>
            <td>税 号：</td>
            <td>$orgTaxNo</td>
            <td>税 号：</td>
            <td>$supInfo[state_tax_code]</td>
          </tr>
          <div>本订单由买卖双方订立，根据订单规定条款，双方同意按下述条款和条件签署订单：</div>
        </table>
      </div>
        
        <div>1、订单文件：<br>本订单所附下列文件是构成合同不可分割的部分</div>
        <div>2、订单明细（以下价格已经包含17%增值税、运输费用及其他所有税费）：</div>
        <table style="width: 100%">
            <thead >
            <tr >
                <th width="26">行号</th>
                <th width="70">料号</th>
                <th width="180">物料名称</th>
                <th width="60">项目</th>
                <th width="65">交期</th>
                <th width="26">数量</th>
                <th width="26">单位</th>
                <th width="40">单价</th>
                <th>金额</th>
            </tr>
            </thead>
            <tbody  >
EOD;
        $po['price_total'] = 0;
        foreach($piList as $i => $pi){
            $confirmDate = date('Y-m-d', $pi['sup_confirm_date']);
            $price = number_format($pi['price'], 2);
            $subTotal = number_format($pi['price_num']*$pi['price'], 2);
            $po['price_total'] += $pi['price_num']*$pi['price'];
            $ln = $i + 1;
            $html .= "<tr class=\"text-small\">
                <td width=\"26\" class=\"content-center\">$ln</td>
                <td width=\"70\">$pi[item_code]</td>
                <td width=\"180\">$pi[item_name]</td>
                <td width=\"60\">$pi[pro_no]</td>
                <td width=\"65\">$confirmDate</td>
                <td width=\"26\" class=\"content-right\">$pi[price_num]</td>
                <td width=\"26\" class=\"content-center\">$pi[price_uom]</td>
                <td width=\"40\" class=\"content-right\">$price</td>
                <td class=\"content-right\">$subTotal</td>
            </tr>";

            // 测试用
            // for($i=1; $i<=5;$i++ ){
            //     $html .= "<tr class=\"text-small\">
            //     <td width=\"26\" class=\"content-center\">$i</td>
            //     <td width=\"70\">$pi[item_code]</td>
            //     <td width=\"180\">$pi[item_name]</td>
            //     <td width=\"60\">$pi[pro_no]</td>
            //     <td width=\"65\">$confirmDate</td>
            //     <td width=\"26\" class=\"content-right\">$pi[price_num]</td>
            //     <td width=\"26\" class=\"content-center\">$pi[price_uom]</td>
            //     <td width=\"40\" class=\"content-right\">$price</td>
            //     <td class=\"content-right\">$subTotal</td>
            // </tr>";
            // }
        }
        $yuan = numbToCnYuan($po['price_total']);
        $po['price_total'] = number_format($po['price_total'], 2);
        $html .= <<<EOD
            </tbody>
            <tfoot>
            <tr>
                <td class="content-center"colspan="2">合计:</td>
                <td class="content-right" colspan="6">$yuan</td>
                <td class="content-right">$po[price_total] </td>
            </tr>
            </tfoot>
        </table>
        <div>3、付款条件：$supInfo[pay_way]</div>
        <div>4、卖方产品质量保证： 
          <div style="padding-left: 10px">
            a.卖方需要提供产品合格证书，产品质量符合我厂要求，质保期从使用之日起一年，或发货之日起18个月，如在质保期内发生质量问题问题，卖方接受无条件退货，并承担相应损失；<br>
            b.按买方图纸要求和材料采购规范《ATW/GF-CLCGGF-2015》生产、检验；<br>
            c.涉及铸造、锻造和热处理的原材料类产品出货需在材料和产品标注“炉号”、材质； 
          </div>
        </div>
        <div>5、产品的交货单位、交货方法、运输方式、到达地点（包括专用线、码头）
          <div style="padding-left: 3em; ">
            a.产品的收货单位：{$orgName}；<br>
            b.包装、交货方法：卖方承担货物最终运达到买方到货地点之间的所有运费，并提供坚固、适合长途运输的包装；<br>
            c.运输方式：快递，送货上门；<br>
            d.到货地点和接货单位（或接货人）：{$orgReceiveAddress}，{$orgReceiver}，{$orgReceiverMobile};
          </div>
        </div>
        <div>6、订单生效：本订单应在双方授权代表签字盖章后立即生效。</div>
        <div>7、此订单其他未尽条款按照买卖双方签订的合同条款执行。</div>

    </div>
</body>
</html>

EOD;
        $sginHtml = <<<EOD
       <div></div>
       <table class="border-none" width="100%">
         <tr >
           <td width="50%">买 方：$orgName</td>
           <td width="50%">卖 方：$supInfo[name]</td>
         </tr>
         <tr >
           <td width="50%">法定代表人或负责人：</td>
           <td width="50%">法定代表人或负责人：</td>
         </tr>
         <tr >
           <td width="50%">日期：$today</td>
           <td width="50%">日期：</td>
         </tr>
       </table>
EOD;


        $sginHtmlHeight = 36.46; // $sginHtml 的高度
        $imgH = $imgW = 50;
        // Print text using writeHTMLCell()
        //$html = iconv('gb2312','utf-8',$html);
        //$pdf->writeHTML($html);
        $pdf->writeHTML($html, true, false, true, false, '');

        $pageHeight = $pdf->getPageHeight();
        $lastY = $pdf->GetY();

        //剩余空间展示放不下 $sginHtml 就新开一页
        if($pageHeight - $lastY < $sginHtmlHeight){
            $pdf->AddPage();
        }
        $pdf->writeHTML($sginHtml, true, false, true, false, '');
        $lastY = $pdf->GetY();
        $imgY = $lastY - 30;
        if($imgY + $imgH >= $pageHeight){
            $imgY -= $imgY + $imgH - $pageHeight;
        }
        //echo "ph=".$pdf->getPageHeight() ;
        //dd($pageHeight);
        //dd($lastY);
        //印章
        $pdf->Image(APP_PATH.'common/static/po_seal.png', 25, $imgY, $imgW, $imgH, '', '', '', false, 300);

        // logo
        $pdf->setPage(1);
        $pdf->Image(APP_PATH.'common/static/po_logo.png', 30, 15, 10, '', '', '', '', false, 300);

        // ---------------------------------------------------------
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$pdf->Output("$po[order_code].pdf" ); //'D'
        $pdf->Output("$po[order_code].pdf", 'D'); //,'D'
        exit();

    }


    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 生产送货单PDF
     * @param $po
     */
    public function downDeliverOrder($po){

        $supInfo = model('SupplierInfo')->findByCode($po['sup_code']);
        if(empty($supInfo)){
            return $this->error('无效的sup_code='.$po['sup_code']);
        }
        $piList = model('PoItem', 'logic')->getListByPoId($po['id']);
        if(count($piList) == 0){
            return $this->error('没有PI,po.id='.$po['id']);
        }

        $orgName = getSysconf('org_name', '苏州安特威阀门有限公司');
        $orgAddress = getSysconf('org_address', '苏州吴江汾湖开发区越秀路988号');
        $orgTel = getSysconf('org_tel', '0512-82880588');
        $orgFax = getSysconf('org_fax', '0512-82079059');
        $orgBankDeposit = getSysconf('org_bank_deposit', '中国农业银行吴江汾湖支行');
        $orgBankAccount = getSysconf('org_bank_account', '10543701040015106');
        $orgTaxNo = getSysconf('org_tax_no', '913205096829757874');
        $orgReceiveAddress = getSysconf('org_receive_address', '苏州吴江汾湖开发区越秀路988号');
        $orgReceiver = getSysconf('org_receiver', '沈斌');
        $orgReceiverMobile = getSysconf('org_receiver_mobile', '13962546667');
        $today = date('Y-m-d', time());

        //dd($supInfo->toJson());
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('wxx@ruitukeji.com');
        $pdf->SetTitle('安特威采购合同');
        $pdf->SetSubject('安特威采购合同');
        $pdf->SetKeywords('安特威,采购合同');


        // set header and footer fonts
        $fontFamly = 'cid0cs';
        $pdf->setHeaderFont([$fontFamly, '', 5]);
        $pdf->setFooterFont([$fontFamly, '', 6]);

        $pdf->SetMargins(10, 10, 10);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        $pdf->setfont($fontFamly);
        // Add a page
        $pdf->AddPage();

        $html = <<<EOD
<!DOCTYPE html>
<html> 
<style type="text/css">
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed,
figure, figcaption, footer, header, hgroup,
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
    margin: 0;
    padding: 0;
    border: none;
    vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure,
footer, header, hgroup, menu, nav, section {
    display: block;
}
body {
    line-height: 1;
}
ol, ul , li {
    list-style: none;
    list-style-type:none;
}
blockquote, q {
    quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
    content: '';
    content: none;
}
table {
    border-collapse: collapse;
    border-spacing: 0;
}
tr, th, td{
  border: 1px solid #000000;
}

tr>th{
  text-align: center;
}

.border-none tr, .border-none th,.border-none td{
  border: none;
}

.content-center{
  text-align: center;
}
.content-left{
  text-align: left;
}
.content-right{
  text-align: right;
}

.text-small{
  font-size: 0.8em;
}

.agreement-form{

}
.agreement-form .top{
 text-align: center;
}

.agreement-form .top *{
  text-align: center;
}

.agreement-form .top h1{
  line-height: 6em;
  font-size: 2em;
}

.agreement-form .top h2{
  line-height: 4em;
  font-size: 1.8em;
}

.agreement-form .top h3{
  line-height: 4em;
  font-size: 1em;
}

.agreement-form .header{
  width: 400px;
  background-color: #2e8ded;
}
.agreement-form .header>table {
  width: 400px;
  background-color: #2e8;
  text-align: left;
}


</style>
</head>

<body>
  <div class="agreement-form">  
    <div class="top">
      <h1>苏 州 安 特 威 阀 门 有 限 公 司</h1>
      <h2>Suzhou Antiwear Valves Co.,Ltd.</h2>
      <h3>电 话：$orgTel  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 传 真：$orgFax</h3>
      <h3>地 址：$orgAddress</h3>
      <h3>Add: 988 Yuexiu Road, Fenho Zone, Wujiang District, Suzhou City, Jiangsu Province, China</h3>
      <p></p><p></p>
      <h1>送 货 单</h1>
    </div>
    <div class=".header">
     <table class="border-none">
      <tr >
        <td width="12%">厂商编码：</td>
        <td width="38%">$supInfo[code]</td>
        <td width="12%">送货日期：</td>
        <td width="38%"></td>
      </tr>
      <tr >
        <td >厂商名称：</td>
        <td >$supInfo[name]</td>
        <td > </td>
        <td > </td>
      </tr>
     </table>
    </div>
        
        <table style="width: 100%">
            <thead >
            <tr >
                <th width="26">行号</th>
                <th width="80">订单号</th>
                <th width="70">料号</th>
                <th width="150">物料名称</th>
                <th width="26">单位</th>
                <th width="26">数量</th>
                <th width="50">实到数量</th>
                <th width="65">炉/批号</th>
                <th >备注</th>
            </tr>
            </thead>
            <tbody  >
EOD;
        $po['price_total'] = 0;
        foreach($piList as $i => $pi){
            $confirmDate = date('Y-m-d', $pi['sup_confirm_date']);
            $price = number_format($pi['price'], 2);
            $subTotal = number_format($pi['price_num']*$pi['price'], 2);
            $po['price_total'] += $pi['price_num']*$pi['price'];
            $ln = $i + 1;
            $html .= "<tr class=\"text-small\">
                <td width=\"26\" class=\"content-center\">$ln</td>
                <td width=\"80\">$po[order_code]</td>
                <td width=\"70\">$pi[item_code]</td>
                <td width=\"150\">$pi[item_name]</td>
                <td width=\"26\" class=\"content-center\">$pi[price_uom]</td>
                <td width=\"26\" class=\"content-right\">$pi[price_num]</td>
                <td width=\"50\" class=\"content-right\"></td>
                <td width=\"65\"></td>
                <td></td>
            </tr>";

            // 测试用
            // for($i=1; $i<=5;$i++ ){
            //     $html .= "<tr class=\"text-small\">
            //     <td width=\"26\" class=\"content-center\">$i</td>
            //     <td width=\"70\">$pi[item_code]</td>
            //     <td width=\"180\">$pi[item_name]</td>
            //     <td width=\"60\">$pi[pro_no]</td>
            //     <td width=\"65\">$confirmDate</td>
            //     <td width=\"26\" class=\"content-right\">$pi[price_num]</td>
            //     <td width=\"26\" class=\"content-center\">$pi[price_uom]</td>
            //     <td width=\"40\" class=\"content-right\">$price</td>
            //     <td class=\"content-right\">$subTotal</td>
            // </tr>";
            // }
        }
        $html .= <<<EOD
            </tbody>
        </table>
    </div>
</body>
</html>
EOD;


        // Print text using writeHTMLCell()
        //$html = iconv('gb2312','utf-8',$html);
        //$pdf->writeHTML($html);
        $pdf->writeHTML($html, true, false, true, false, '');
        // logo
        $pdf->setPage(1);
        $pdf->Image(APP_PATH.'common/static/po_logo.png', 30, 15, 10, '', '', '', '', false, 300);

        // ---------------------------------------------------------
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$pdf->Output("$po[order_code].pdf" ); //'D'
        $pdf->Output("$po[order_code]_DO.pdf",'D'); //,'D'
        exit();

    }
}