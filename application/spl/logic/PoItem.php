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
        $field=[
            'pi.*',
            'pr.pro_no',
        ];
        $list = self::alias('pi')
            ->join('u9_pr pr','pr.id = pi.pr_id')
            ->where('pi.po_id', $po_id)
            ->field($field)
            ->select();
        //dd(self::getLastSql());
        return $list;
    }
}