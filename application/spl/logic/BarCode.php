<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;


use barcodegen\BCGUtil;
use service\HttpService;
use TCPDF;

class BarCode extends BaseLogic{
    protected $table = 'atw_bar_code';

    /**
     * 根据code查找
     */
    public static function findByCode($code){
        return self::where('lot_no', $code)->find();
    }

    /**
     * 保存条形码
     *
     *  $printParmas = [
     * 'LotNo' => '',                        //  物料条码
     * 'ItemCode' => $pi['item_code'],       //物料编码
     * 'ItemName' => $pi['item_name'],       //物料名称
     * 'ItemStd' => $item['specs'],                      //物料规格
     * 'MaterialTexture' => $item['mat_quality'],          //材质
     * 'Quantity' => $reqParams['num'],         //数量
     * 'MeasurementUnit' => $pi['price_uom'],          //计量单位
     * 'ManufactureDate' => $reqParams['facture_date'],            //生产日期
     * 'HeatNumber' => $reqParams['heat_no'],           //炉号
     * 'VendorName' => $pi['sup_name'],           //供应商名称
     * 'Remark' => $reqParams['remark'],           //备注
     * ];
     */
    public function saveBarCode($printParmas){
        $todayStart = getTodayStartTime();
        $todayEnd = getTodayEndTime();
        $todayNoCunt = $this->whereBetween('create_at', [$todayStart, $todayEnd])->count();
        $lotNo = 'LL-'.date('Ymd').sprintf('-%03s', ++$todayNoCunt);
        $printParmas['LotNo'] = $lotNo;
        $u9Ret = HttpService::post(getenv('APP_API_U9').'index/generatingBarCode', $printParmas);
        $u9Ret = json_decode($u9Ret, true);
        if(empty($u9Ret)){
            return resultArray(6000);
        }

        if($u9Ret['code'] != 2000 || !$u9Ret['result']['IsSuccess']){
            return resultArray($u9Ret);
        }

        $barCode = [
            'lot_no' => $printParmas['LotNo'],
            'item_code' => $printParmas['ItemCode'],
            'item_name' => $printParmas['ItemName'],
            'item_std' => $printParmas['ItemStd'],
            'material_texture' => $printParmas['MaterialTexture'],
            'quantity' => $printParmas['Quantity'],
            'measurement_unit' => $printParmas['MeasurementUnit'],
            'manufacture_date' => $printParmas['ManufactureDate'],
            'heat_number' => $printParmas['HeatNumber'],
            'vendor_name' => $printParmas['VendorName'],
            'remark' => $printParmas['Remark'],
            'sup_code' => session('spl_user')['sup_code']
        ];

        if($this->create($barCode)){
            return resultArray(2000, '', ['lot_no' => $lotNo]);
        }

        return resultArray(5020, '', $u9Ret);
    }

    /**
     * 打印条形码
     *
     *  $printParmas = [
     * 'LotNo' => '',                        //  物料条码
     * 'ItemCode' => $pi['item_code'],       //物料编码
     * 'ItemName' => $pi['item_name'],       //物料名称
     * 'ItemStd' => $item['specs'],                      //物料规格
     * 'MaterialTexture' => $item['mat_quality'],          //材质
     * 'Quantity' => $reqParams['num'],         //数量
     * 'MeasurementUnit' => $pi['price_uom'],          //计量单位
     * 'ManufactureDate' => $reqParams['facture_date'],            //生产日期
     * 'HeatNumber' => $reqParams['heat_no'],           //炉号
     * 'VendorName' => $pi['sup_name'],           //供应商名称
     * 'Remark' => $reqParams['remark'],           //备注
     * ];
     */
    public function printBarCode($barCode){

        $barCode['manufacture_date_fmt'] = date('Y-m-d',$barCode['manufacture_date']);
        $codePath = BCGUtil::generateCode($barCode['lot_no'], false);
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

        $pdf->SetMargins(40, 10, 40);
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

.agreement-form  table tr td.label {
  line-height:  24px;
  background-color: gainsboro;
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
  <div class="agreement-form" >  
    <div>物料编号：</div>
    <div class="content-center">$barCode[lot_no]</div>
      <table style="width: 100%">
        <tbody>
        <tr style="height: 24px" >
          <td width="60" class="label"  >规格型号</td>
          <td width="125"  style="padding-left: 10px" >$barCode[item_std]</td>
          <td width="60" class="label"  >名称</td>
          <td width="125"  >$barCode[item_name]</td>
        </tr>
        <tr style="height: 24px" >
        <td class="label" >数量($barCode[measurement_unit])</td>
        <td  >$barCode[quantity]</td>
        <td class="label" >材质</td>
        <td  >$barCode[material_texture]</td>
        </tr>
        <tr style="height: 24px">
          <td class="label" >生产日期</td>
          <td>$barCode[manufacture_date_fmt]</td>
          <td class="label">炉号</td>
          <td>$barCode[heat_number]</td>
        </tr>
        <tr>
          <td class="label">供应商</td>
          <td colspan="3">$barCode[vendor_name]</td>
        </tr>
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
        //$pdf->Image(APP_PATH.'common/static/po_logo.png', 30, 15, 10, '', '', '', '', false, 300);
        $pdf->Image($codePath, 70, 19, 100, 10, '', '', '', false, 300);

        // ---------------------------------------------------------
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        //$pdf->Output("$po[order_code].pdf" ); //'D'
        $pdf->Output("$barCode[lot_no].pdf"); //,'D'
        exit();

    }
}