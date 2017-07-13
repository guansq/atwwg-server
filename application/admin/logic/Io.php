<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 9:57
 */

namespace app\admin\logic;

use app\common\model\Io as IoModel;

class Io extends BaseLogic{

    protected $table = 'atw_io';
    const STATUS_ARR = [
        'init' => '未报价',
        'quoted' => '已报价',
        'winbid_uncheck' => '待审核',
        'wait' => '待下单',
        'winbid' => '中标',
        'losebid' => '未中标',
        'un_tender' => '未投标',
    ];

    function getIoList($start, $length, $where){
        //->join('u9_pr c','a.pr_code=c.pr_code','LEFT'),c.pro_no
        $list = IoModel::alias('a')
            ->field('a.*,b.desc,pr.pro_no,pr.status as pr_status,pr.inquiry_way')
            ->join('item b', 'a.item_code=b.code', 'LEFT')
            ->join('u9_pr pr', 'pr.id = a.pr_id', 'LEFT')
            ->limit("$start,$length")
            ->order('pr.update_at desc')
            ->group('pr_id');
        if(!empty($where)){
            $list = $list->where($where);
        }
        $list = $list->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     * 得到不含分页的数据
     */
    function getIoAllList($where){
        //->join('u9_pr c','a.pr_code=c.pr_code','LEFT'),c.pro_no
        $list = IoModel::alias('a')
            ->field('a.*,b.desc,pr.pro_no,pr.status as pr_status ,pr.inquiry_way')
            ->join('item b', 'a.item_code=b.code', 'LEFT')
            ->join('u9_pr pr', 'pr.id = a.pr_id', 'LEFT')
            ->group('pr_id');
        if(!empty($where)){
            $list = $list->where($where);
        }
        $list = $list->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }

    function getListNum($where = []){
        $count = IoModel::alias('a')
            ->field('a.id')
            ->join('item b', 'a.item_code=b.code', 'LEFT')
            ->join('u9_pr pr', 'pr.id = a.pr_id', 'LEFT')
            ->group('pr_id');
        if(!empty($where)){
            $count = $count->where($where);
        }
        $count = $count->count();
        //echo $this->getLastSql();die;
        //$count = IoModel::alias('a')->field('a.*,b.desc')->join('item b','a.item_code=b.code','LEFT')->group('pr_code,item_code')->count();
        return $count;
    }

    function getIoCountByWhere($where){
        $count = IoModel::where($where)->count();
        return $count;
    }

    function getIoInfo($io_code){
        $list = IoModel::alias('a')
            ->field('a.*,b.ctc_name,b.mobile,b.phone,b.email')
            ->join('supplier_info b', 'a.sup_code=b.code', 'LEFT')
            ->where('io_code', "$io_code")
            ->select();
        //->join('u9_pr c','a.pr_code=c.pr_code','LEFT'),c.pro_no
        //echo $this->getLastSql();
        if($list){
            $list = collection($list)->toArray();
        }

        foreach($list as &$item){
            $item['statusStr'] = self::STATUS_ARR[$item['status']];
            $item['totalPrice'] = '';
            if(!empty($item['quote_price'])){
                $item['totalPrice'] = atwMoney($item['quote_price']*$item['price_num']);
            }
         }
        return $list;
    }

    /*
     * 得到询价待审批数量
     */
    function getQuoteNum(){
        return IoModel::where('status', 'in', ['quoted'])->count();
    }

    /*
     *得到流拍询价数量
     */
    function getGiveupNum(){
        return IoModel::where('status', 'in', ['giveupbid'])->count();
    }

    /*
     * 通过条件得到询价单数量
     */
    function getIoNumByWhere($where){
        return IoModel::where($where)->count();
    }

    /*
     * 获取supid通过ioid
     */
    function getSupId($where){
        $list = IoModel::alias('a')
            ->field('b.sup_id,b.phone,b.email')
            ->join('supplier_info b', 'a.sup_code=b.code', 'LEFT')
            ->where($where)
            ->find();
        if($list){
            $list = $list->toArray();
        }
        return $list;
    }

    /*
     * 得到一条Io记录
     */
    function getIoRecord($where){
        $info = IoModel::where($where)->find();
        if($info){
            $info  = $info->toArray();
        }
        return $info;
    }

    /*
     * 更新Io
     */
    function updateIo($where,$data){
        return IoModel::where($where)->update($data);
    }
}