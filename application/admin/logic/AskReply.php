<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/7
 * Time: 13:17
 */
namespace app\admin\logic;
use app\common\model\AskReply as AskReplyModel;
use think\Request;
class AskReply extends BaseLogic{
    /*
     * 得到所有的问答
     */
    public function getAllAsk($ispage = false,$get,$row_page = 10){
        $askRely = model('AskReply');

        if($ispage){
            $list = $askRely->where("type","ask")->order('id desc')->paginate($row_page,true);
            /*if(!empty($get)){
                $list = $list->where($get);
            }*/
        }else{
            $list = $askRely->where("type","ask")->order('id desc')->select();

        }
        if(!empty($get)){
            $list = $list->where($get);
        }
        /*if($list){
            $list = collection($list)->toArray();
        }*/
        //$page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-load="$1" href="javascript:void(0);"', 'pagination pull-right'], $page->render());
        return $list;
    }
}