<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;


use app\common\util\Page;

class PoItem extends BaseLogic{
    protected $table = 'atw_po_item';

    /**
     * 得到订单下的item列表
     */
    public static function getListByPoId($po_id){
        $list = self::where('po_id', $po_id)->select();
        //dd(self::getLastSql());
        return $list;
    }


    /**
     * Author: WILL<314112362@qq.com>
     * Describe:
     * @param $piIds
     * @param $bizType
     */
    function getPoItemPage($searchKwd, $startpageIndex = 1, $length = 10){
        $page = new Page($startpageIndex, $length);
        $fields = [
            'pi.po_id',
            'pi.po_code',
            'pi.po_ln',
            'pi.item_code',
            'pi.item_name',
            'pi.sup_code',
            'pi.sup_name',
            'pi.price_num',
            'pi.price_uom',
            'pi.tc_num',
            'pi.tc_uom',
            'pi.pr_id',
            'pi.pr_code',
            'pi.pr_ln',
            'pi.sup_confirm_date',
            'pi.req_date',
            'pi.sup_update_date',
            'pi.price',
            'pi.amount',
            'pi.tax_rate',
            'pi.purch_code',
            'pi.purch_name',
            'pi.arv_goods_num',
            'pi.pro_goods_num',
            'pi.return_goods_num',
            'pi.fill_goods_num',
            'pi.create_at',
            'pi.update_at',
            'pi.status',
            'pi.u9_status',
            'pi.last_sync_time',
            'pi.winbid_time',
            'pr.pro_no',
        ];
        $where = []; // 查询条件
        if(!empty($searchKwd['status']) && $searchKwd['status'] != 'all'){
            $where['po.status'] = $searchKwd['status'];
        };


        if(!empty($searchKwd['contract_begintime'])){
            $where['po.contract_time'] = ['>=', $searchKwd['contract_begintime']];
        };
        if(!empty($searchKwd['contract_endtime'])){
            $where['po.contract_time'] = ['<=', $searchKwd['contract_endtime']];
        };


        if(!empty($searchKwd['sup'])){
            $where['pi.sup_code'] = $searchKwd['sup'];
        };

        $total = $this->alias('pi')
            ->join('atw_po po', 'po.id = pi.po_id', 'LEFT')
            ->join('atw_u9_pr pr', 'pr.id = pi.pr_id', 'LEFT')
            ->where($where)
            ->whereNotNull('pi.po_code')
            ->count();

        $page->setItemTotal($total);
        $itemList = $this->alias('pi')
            ->join('atw_po po', 'po.id = pi.po_id', 'LEFT')
            ->join('atw_u9_pr pr', 'pr.id = pi.pr_id', 'LEFT')
            ->where($where)
            ->whereNotNull('pi.po_code')
            ->order('pi.update_at', 'DESC')
            ->limit($page->getItemStart(), $length)
            ->field($fields)
            ->select();
        foreach($itemList as &$item){
            $item['req_date_fmt'] = empty($item['req_date']) ? "" : date('Y-m-d', $item['req_date']);
            $item['sup_confirm_date_fmt'] = empty($item['sup_confirm_date']) ? "" : date('Y-m-d', $item['sup_confirm_date']);
            $item['sup_update_date_fmt'] = empty($item['sup_update_date']) ? "" : date('Y-m-d', $item['sup_update_date']);
            $item['tc_num_fmt'] = number_format($item['tc_num'], 2);
            $item['price_fmt'] = number_format($item['price'], 2);
            $item['price_subtotal_fmt'] = number_format($item['price']*$item['tc_num'], 2);
            $item['arv_goods_num_fmt'] = number_format($item['arv_goods_num'], 2);
            $item['pro_goods_num_fmt'] = number_format($item['pro_goods_num'], 2);
        }
        $page->setItemList($itemList);
        return $page;
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

    /** todo 验证
     * Author: W.W <will.wxx@qq.com>
     * Describe: 统计重复的 pr_code pr_ln 数量
     * @param $idArr
     */
    public function hasRepeatedPrByPiIds($idArr){
        $ids = implode(',',$idArr);
        $sql ="SELECT COUNT(*) AS cnt FROM {$this->table} WHERE id IN ($ids) GROUP BY pr_code,pr_ln  HAVING cnt>1";
        $dbRet = $this->execute($sql);
        return intval($dbRet);
    }
}