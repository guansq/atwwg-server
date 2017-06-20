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
use TCPDF;

class Order extends BaseLogic{
    /*
     * 得到订单列表
     */
    function getPolist($where){
        if(empty($where)){
            $list = Po::order('update_at DESC')->select();
        }else{
            $list = Po::where($where)->order('update_at DESC')->select();
        }
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
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
        if($list){
            $list = collection($list)->toArray();
        }
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
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改订单状态
    function updateStatus($id, $status = 'sup_cancel'){
        $list = Po::where(['id' => $id])->update(['status' => $status]);
        return $list;
    }

    //更新交期时间
    function updateSupconfirmdate($id, $supconfirmdate, $supconfirmdate){
        $list = PoItem::where(['id' => $id])->update([
            'sup_confirm_date' => $supconfirmdate,
            'update_at' => time(),
            'sup_update_date' => $supconfirmdate
            //'status' => 'uncheck'//改成未审核
        ]);

        return $list;
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
                $list = PoItem::where(['po_id' => $po_id])->select();
            }else{
                $list = PoItem::where(['po_id' => $po_id, 'item_code' => $item_code])->select();
            }
            //echo $this->getLastSql();//die;
            if($list){
                $list = collection($list)->toArray();
                return $list;
            }
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
        return PoItem::alias('a')
            ->join('po b', 'a.po_id = b.id')
            ->where('b.status', 'in', ['executing'])
            ->where('pro_goods_num', '>', 0)
            ->where('a.sup_code', $sup_code)
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
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('wxx@ruitukeji.com');
        $pdf->SetTitle('安特威采购合同');
        $pdf->SetSubject('安特威采购合同');
        $pdf->SetKeywords('安特威,采购合同');

        // set default header data
        //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        //$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // set header and footer fonts
        $fontFamly = 'cid0cs';
        $pdf->setHeaderFont([$fontFamly, '', 5]);
        $pdf->setFooterFont([$fontFamly, '', 6]);

        // set default monospaced font
        // $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set default font subsetting mode
        //$pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        //$pdf->SetFont('dejavusans', '', 14, '', true);
        $pdf->setfont($fontFamly);
        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        /*$pdf->setTextShadow(array(
            'enabled' => true,
            'depth_w' => 0.2,
            'depth_h' => 0.2,
            'color' => array(196, 196, 196),
            'opacity' => 1,
            'blend_mode' => 'Normal'
        ));*/

        // Set some content to print
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
    border: 0;
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
ol, ul {
    list-style: none;
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

/*================================================签订电子合同======================================*/
.agreement-form  .title{
    font-size: 1em;
    font-weight: bold;
    text-align: center;
}
</style>
</head>

<body>
    <div class="atv-main">
        <div class="agreement-form ">
                <div class="title">采购合同</div>
                <div class="sm">
                    <div class="rig">合同编号：<span class="drag-in">[系统带入（ERP生成）]</span></div>
                </div>
                <div class="sm">
                    <div class="rig">签订时间：<span class="drag-in">[系统带入]</span>年<span class="drag-in">[系统带入]</span>月<span class="drag-in">[系统带入]</span>日</div>
                </div>
                <div class="buyer">买方：<span class="drag-in">[系统带入]</span></div>
                <div class="seller">卖方：苏州安特威阀门有限公司</div>
                <div class="content">
                    经过双方友好协商，依据《中华人民共和国合同法》及其他相关法律规定，
                    买卖双方同意签订以下合同条款，以便双方共同遵守、履行合同。
                </div>
                <div class="details-of-contract ">
                    <div class="name">一、供货明细：</div>
                    <table>
                        <thead>
                        <tr>
                            <td>序号</td>
                            <td>名称</td>
                            <td>位号</td>
                            <td>型号</td>
                            <td>单价</td>
                            <td>数量</td>
                            <td>小计</td>
                            <td>备注</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                            <td><span class="drag-in">[系统带入]</span></td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td></td>
                            <td colspan="4">合计（RMB）<span class="drag-in">[系统带入]</span>元整</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="name">二、运输</div>
                    <p>由卖方负责运输货物(陆运)运费由卖方承担</p>
                    <div class="name">三、交货期限、交货地点：</div>
                    <p>合同生效之日起签订<span class="drag-in">[系统带入]</span>天发货。</p>
                    <p>合同生效： 合同签订之日起</p>
                    <div class="name">四、货款金额及结算方式：</div>
                    <p>合同总价为： <span class="drag-in">[系统带入]</span> 元整( <span class="drag-in">[系统带入]</span>元整)，该总价含17%增值税.</p>
                    <p>货款结算方式：全款发货。</p>
                    <p>付款方式 ： 现金支付。</p>
                    <div class="name">五、质量保证、检验：</div>
                    <p>1. 产品必须符合现行国家标准、行业标准或双方约定的标准。</p>
                    <p>2. 质保期：安装使用12个月或发货后18个月内，以先到者为准。</p>
                    <div class="name">六、不可抗力：</div>
                    <p>在合同执行期间，如发生地震、洪灾、暴动等不可抗力因素致使合同不能正常履行，一方应及时通知对方，并在15天内提供政府机关或相应部门出具的证明材料，双方可以协商解除合同或其他相关事宜。</p>
                    <div class="name">七、争议解决方式：</div>
                    <p>凡因本合同的效力、履行、解释等发生的一切争议，双方可先行友好解决，协商不成时，可向买方所在地相应级别的人民法院提起诉讼。</p>
                    <div class="name">八、生效及其他：</div>
                    <p> 1. 本合同自双方签字并盖章之日起生效，一式  贰 份，买方执 壹 份、卖方执  壹 份，本合同传真件有效，涂改无效。</p>
                    <p>2. 如有未尽事宜由双方共同协商，签订补充协议，补充协议与本合同具有同等法律效力；</p>
                    <p>3.本合同附件技术协议与合同同样具备法律效力。</p>
                </div>
                <div class="conclude-and-sign">
                    <div class="left">
                        <p>买方</p>
                        <ul>
                            <li><span>名称：</span><input type="text"></li>
                            <li><span>地址：</span><input type="text"></li>
                            <li><span>签约代表：</span><input type="text"></li>
                            <li><span>签订日期：</span><input type="text"></li>
                            <li><span>开户行：</span><input type="text"></li>
                            <li><span>开户账号：</span><input type="text"></li>
                            <li><span>税号：</span><input type="text"></li>
                            <li><span>联系人：</span><input type="text"></li>
                        </ul>
                    </div>
                    <div class="right">
                        <p>卖方</p>
                        <ul>
                            <li><span>名称：</span>苏州安特威阀门有限公司 </li>
                            <li><span>地址：</span>江苏省吴江汾湖经济开发区越秀路988号 </li>
                            <li><span>签约代表：</span><input type="text"></li>
                            <li><span>签订日期：</span><input type="text"></li>
                            <li><span>开户行：</span>中国农业银行吴江汾湖支行</li>
                            <li><span>开户账号：</span>10543701040015106</li>
                            <li><span>税号：</span>913205096829757874</li>
                            <li><span>联系人：</span><input type="text"></li>
                        </ul>
                    </div>
                </div>
            </div>
    </div>
</body>
</html>
EOD;

        // Print text using writeHTMLCell()
        //$html = iconv('gb2312','utf-8',$html);
        $pdf->writeHTML($html);

        // ---------------------------------------------------------

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $pdf->Output('example_001.pdf', 'I');
        exit();

    }
}