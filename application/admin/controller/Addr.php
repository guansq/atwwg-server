<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\DataService;
use app\admin\model\AddrModel;
use think\Db;

class Addr extends BasicAdmin{
    protected $table = 'SystemArea';
    protected $title = '地区管理';

    public function index(){
        $pid = $this->request->get('pid', '0');
        $name = $this->request->get('name');
        //$this->title = '地区管理';
        if(empty($name)){
            $where = ['pid'=>$pid];
        }else{
            $where = ['name'=>['like','%'.$name.'%']];
        }
        //dump($where);
        $addr = AddrModel::getAddrInfo($where);
        $this->assign('list',$addr);
        $this->assign('title',$this->title);
        return view();
    }

    public function del(){
        $ids = explode(',', input("post.id", ''));
        $isrelease = true;
        foreach($ids as $v){
            $isHaveRecord = AddrModel::haveChild($v);
            if(!empty($isHaveRecord)){
                $isrelease = false;
                break;
            }
        }
        //dump($isrelease);
        if(!$isrelease){
            $this->error("请删除该地址下面的子地址！");
        }
        //halt($field);
        if (DataService::update($this->table)) {
            LogService::write('地区管理', '删除地区');
            $this->success("地址删除成功！", '');
        }
        $this->error("地址删除失败，请稍候再试！");
    }

    public function add(){
        $id = $this->request->get('id', '0');
        $where = ['id'=>$id];
        if(request()->isPost()){
            $data=input('param.');
            if(empty($data['merger_name'])){
                $data['merger_name'] = $data['name'];
            }else{
                $data['merger_name'] .= $data['name'];
            }
            unset($data['id']);
            //halt($data);
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            if (false !== $result) {
                LogService::write('地区管理', '添加地区');
                $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
            }
            return $result;
        }else{
            $addr = AddrModel::getAddrInfo($where);
            if(empty($id)){
                $uptitle = '添加顶级地区';
                $merger_name = '';
                $level = 0;
                $pid = 0;
            }else{
                $uptitle = '上级地区为：'.$addr[0]['name'];
                $merger_name = $addr[0]['merger_name'];
                $level = $addr[0]['level'] + 1;
                $pid = $addr[0]['id'];
            }
            $this->assign('uptitle',$uptitle);
            $this->assign('merger_name',$merger_name);
            $this->assign('level',$level);
            $this->assign('pid',$pid);
            return $this->_form($this->table, 'form');
        }
    }

    public function test(){
        echo '11111111111';
    }
}