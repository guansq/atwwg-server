<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;

use TCPDF;
use think\Model;

class PoReceive extends Model{
    protected $table = '';

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 记录 rcv
     * @param $params
     */
    function createReturnCode($params){
        $poLogic = model('Order', 'logic');
        $now = time();
        $saveData = [];
        $seq = $this->where('po_id',$params['id'])->order('seq','DESC')->group('seq')->count()+1;
        $rcvCode = 'RCV-'.date('Ymd').'-'. $params['id'].sprintf('%03s', $seq);

        foreach($params['rcv'] as $piId => $pi){
            if(empty($pi['num'])){
                continue;
            }
            $saveData[] = [
                'po_id' => $params['id'],
                'rcv_code' => $rcvCode,
                'pi_id' => $piId,
                'seq' => $seq,
                'rcv_num' => $pi['num'],
                'heat_code' => $pi['heat_code'],
                'remark' => $pi['remark'],
                'create_at' => $now,
                'update_at' => $now,
            ];
        }
        if(empty($saveData)){
            return resultArray(4001);
        }
        if(!$this->saveAll($saveData)){
            return resultArray(5020);
        };
        return resultArray(2000, '', ['rcvCode'=>$rcvCode]);
    }



    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 生产送货单PDF
     * @param $po
     */
    public function downPoReceive($rcvCode){
        $rcvList = $this->where('rcv_code',$rcvCode)->select();
        $piLogic = model('PoItem','logic');
        $today = date('Y-m-d');
        if(empty($rcvList)){
            exit('<script>window.close();</script>');
        }
        $po = model('Order', 'logic')->find($rcvList[0]['po_id']);
        $supInfo = model('SupplierInfo')->findByCode($po['sup_code']);
        if(empty($supInfo)){
            return $this->error('无效的sup_code='.$po['sup_code']);
        }
        $orgAddress = getSysconf('org_address', '苏州吴江汾湖开发区越秀路988号');
        $orgTel = getSysconf('org_tel', '0512-82880588');
        $orgFax = getSysconf('org_fax', '0512-82079059');

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
        $fontFamly = 'stsongstdlight';
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
        <td >送货单号： </td>
        <td >$rcvCode</td>
      </tr>
     </table>
    </div>
        
        <table style="width: 100%">
            <thead >
            <tr >
                <th width="26">行号</th>
                <th width="70">订单号</th>
                <th width="65">料号</th>
                <th width="150">物料名称</th>
                <th width="26">单位</th>
                <th width="50">数量</th>
                <th width="50">实到数量</th>
                <th width="65">炉/批号</th>
                <th >备注</th>
            </tr>
            </thead>
            <tbody  >
EOD;
        foreach($rcvList as $i => $rcv){
            $pi = $piLogic->find($rcv['pi_id']);
            $ln = $i + 1;
            $html .= "<tr class=\"text-small\">
                <td width=\"26\" class=\"content-center\">$ln</td>
                <td width=\"70\">$po[order_code]</td>
                <td width=\"65\">$pi[item_code]</td>
                <td width=\"150\">$pi[item_name]</td>
                <td width=\"26\" class=\"content-center\">$pi[tc_uom]</td>
                <td width=\"50\" class=\"content-right\">$rcv[rcv_num]</td>
                <td width=\"50\" class=\"content-right\"></td>
                <td width=\"65\">$rcv[heat_code]</td>
                <td>$rcv[remark]</td>
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
        $pdf->Output("$rcvCode.pdf"); //,'D'
        exit();

    }
}