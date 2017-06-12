<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;
use think\Model;
use app\common\model\Io as IoModel;
use app\common\model\Po;
use app\common\model\PoItem;

class Offer extends BaseLogic{

    protected $table = 'atw_io';
    protected $STATUS_ARR = [
        'init' => '未报价',
        'quoted' => '已报价',
        'winbid' => '中标',
        'giveupbid' => '弃标',
        'close' => ' 关闭',
    ];
    //获得报价中心列表
    function getOfferInfo($sup_code,$where=''){
        if(!empty($where)){
            $list = IoModel::where('sup_code',"$sup_code")->where($where)->order('create_at desc')->select();
        }else{
            $list = IoModel::where('sup_code',"$sup_code")->order('create_at desc')->select();
        }
      //  echo $this->getLastSql();//die;
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
    //更改交期
    function updateData($key,$dataArr){
        $result = model('Io')->where('id',$key)->update($dataArr);
        //echo $this->getLastSql();die;
        return $result;
    }
    //获取报价单条信息
    function getOneById($Id){
        $result = IoModel::where('id',$Id)->find($Id);
        return $result;
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 如果请购单的 供应商已经全部报完价了，则该状态为 已报价
     * @param $id
     */
    public function updatePrStatusById($id){
        $dbRet = $this->field('pr_id')->where('id', $id)->group('pr_id')->find();
        $count = $this->where('pr_id', $dbRet['pr_id'])->where('status', 'init')->count();
        if($count == 0){
            model('PR', 'logic')->updateStatus(['id' => $dbRet['pr_id']], 'quoted');
        }
        return $count;
    }
}