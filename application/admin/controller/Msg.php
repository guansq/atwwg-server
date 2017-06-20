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
        $userLogic = model('SystemUser','logic');
        $curInfo = $askReplyLogic->getAskInfo($id);
        $replyList = $askReplyLogic->getAllReply($id);
        $replayArr = [
            $curInfo['sender_id'] => 'left',
            $curInfo['sendee_id'] => 'right'
        ];
        $askReplyLogic->updateReadAt(['id' => $id],['read_at' => time()]);//打开的阅读时间进行更新
        foreach($replyList as $k => $v){
            //得到用户头像
            if($v['sender_id'] == 0){
                $replyList[$k]['avatar'] = '';//系统默认头像
                $replyList[$k]['name'] = '系统管理员';
            }else{
                $replyList[$k]['avatar'] = $userLogic->getAvatar(['id'=>$v['sender_id']]);//得到用户头像/static/admin/img/personal.png
                $replyList[$k]['name'] = $userLogic->getName(['id'=>$v['sender_id']]);//得到用户头像/static/admin/img/personal.png
            }

            if(key_exists($v['sender_id'],$replayArr)){
                $replyList[$k]['position'] = $replayArr[$v['sender_id']];
            }

            $askReplyLogic->updateReadAt(['id' => $v['id']],['read_at' => time()]);//打开的阅读时间进行更新
        }
        $this->assign('title',$this->title);
        $this->assign('askInfo',$curInfo);
        $this->assign('replyList',$replyList);
        return view();
    }

    public function sendMsg(){
        $data = input('param.');
        $askReplyLogic = model('AskReply','logic');
        $dataArr = [];
        $dataArr['content'] = $data['content'];
        $dataArr['type'] = 'reply';
        $dataArr['pid'] = $data['pid'];
        $dataArr['sender_id'] = 0;
        $dataArr['sendee_id'] = $data['sender_id'];
        $dataArr['create_at'] = time();
        $dataArr['update_at'] = time();
        $res = $askReplyLogic->saveSmg($dataArr);
        if($res){
            return json(['code'=>2000,'msg'=>'回复成功','data'=>[]]);
        }else{
            return json(['code'=>4000,'msg'=>'回复失败','data'=>[]]);
        }
    }
}