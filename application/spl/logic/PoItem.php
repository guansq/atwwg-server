<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;


class PoItem extends BaseLogic{
    protected $table = 'atw_po_item';

    /**
     * 得到订单下的item列表
     */
    public static function getListByPoId($po_id){
        $field = [
            'pi.*',
            'pr.pro_no',
        ];
        $list = self::alias('pi')
            ->join('u9_pr pr', 'pr.id = pi.pr_id')
            ->where('pi.po_id', $po_id)
            ->field($field)
            ->select();
        //dd(self::getLastSql());
        return $list;
    }

    /**
     * 打印条形码
     *
     *  $printParmas = [
     * 'LotNo' => '',                        //todo 物料条码
     * 'ItemCode' => $pi['item_code'],       //物料编码
     * 'ItemName' => $pi['item_name'],       //物料名称
     * 'ItemStd' => $item['specs'],                      //物料规格
     * 'MaterialTexture' => $item['mat_quality'],          //材质
     * 'Quantity' => $reqParams['num'],         //数量
     * 'MeasurementUnit' => $pi['price_uom'],          //计量单位
     * 'ManufactureDat' => $reqParams['facture_date'],            //生产日期
     * 'HeatNumber' => $reqParams['heat_no'],           //炉号
     * 'VendorName' => $pi['sup_name'],           //供应商名称
     * 'Remark' => $reqParams['remark'],           //备注
     * ];
     */
    public static function printBarCode($barCode){
        dd(getTodayStartTime());
        $lotNo = '';
        $todayCnt = '';

    }
}