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
        return $list;
    }

    /*
     * 得到询问ID信息
     */
    public function getAskInfo($id){
        $askRely = model('AskReply');
        $info = $askRely->where("id","$id")->find();
        if($info){
            $info = $info->toArray();
        }
        return $info;
    }
    /*
     * 得到询问ID下的所有回复
     */
    public function getAllReply($id){
        $askRely = model('AskReply');
        $list = $askRely->where("pid","$id")->order('create_at desc')->select();
        if($list){
            $list = collection($list)->toArray();
        }
        return $list;
    }
    /*
     * 更新阅读时间
     */
    public function updateReadAt($where,$data){
        $askRely = model('AskReply');
        return $askRely->where($where)->update($data);
    }

    /*
     * 得到消息未读数量
     */
    public function getUnreadNum(){
        $askRely = model('AskReply');
        return $askRely->field('read_at')->where('read_at','')->count();
    }
}