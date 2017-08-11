<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;


use service\HttpService;

class BarCode extends BaseLogic{
    protected $table = 'atw_bar_code';

    /**
     * 根据code查找
     */
    public static function findByCode($code){
        return self::where('code', $code)->find();
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
        ];

        if($this->create($barCode)){
            return resultArray(2000, '', ['lot_no' => $lotNo]);
        }

        return resultArray(5020, '', $u9Ret);
    }
}