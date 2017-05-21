<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 14:08
 */
namespace app\admin\controller;

use service\LogService;
use service\DataService;

class Article extends BaseController{
    protected $table = 'SystemArticle';
    protected $title = '文章管理';

    function index(){

        $this->assign('title',$this->title);
        return view();
    }

    function add(){
        if(request()->isPost()){
            $data=input('param.');
            $result = $this->validate($data,'Article');
            if($result !== true){
                $this->error($result);
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            LogService::write('Article管理', '上传Article成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            return view();
        }
    }

    function del(){
        return view();
    }
}