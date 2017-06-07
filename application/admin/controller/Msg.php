<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/6
 * Time: 15:01
 */
namespace app\admin\controller;
use think\Db;

class Msg extends BaseController{
    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'AskReply';

    public function index(){
        $this->title = '咨询回复列表';
        $askReplyLogic = model('AskReply','logic');
        $ispage = true;
        $row_page = $this->request->get('rows', cookie('rows'), 'intval');
        cookie('rows', $row_page >= 10 ? $row_page : 20);
        $getArr = $this->request->get();
        $get = [];
        $list = $askReplyLogic->getAllAsk($ispage,$get,$row_page);
        $page = '';
        if($ispage){
            $page = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-load="$1" href="javascript:void(0);"', 'pagination pull-right'], $list->render());
            $list = $list->all();
        }
        if($list){
            $list = collection($list)->toArray();
        }
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('title',$this->title);
        return view();
    }

    public function detail(){
        $this->title = '咨询回复详情';
        $id = input('param.id');
        $askReplyLogic = model('AskReply','logic');
        $curInfo = $askReplyLogic->getAskInfo($id);
        $replyList = $askReplyLogic->getAllReply($id);
        $replayArr = [
            $curInfo['sender_id'] => 'left',
            $curInfo['sendee_id'] => 'right'
        ];
        foreach($replyList as $k => $v){
            if(key_exists($v['sender_id'],$replayArr)){
                $replyList[$k]['position'] = $replayArr[$v['sender_id']];
            }
        }
        $this->assign('title',$this->title);
        $this->assign('askInfo',$curInfo);
        //dump($replyList);
        //echo $id;
        return view();
    }
}