<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use app\admin\model\AddrModel;
use think\db;

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
        $addr = AddrModel::getAddrInfoByPid($where);
        $this->assign('list',$addr);
        $this->assign('title',$this->title);
        return view();
    }

    public function del(){
        $id = $this->request->get('id', '0');
        if($this->request->isPost){

        }else{

        }
    }

    public function add(){
        $id = $this->request->get('id', '0');
        $where = ['pid'=>$id];
        if($this->request->isPost){

        }else{
            $addr = AddrModel::getAddrInfoByPid($where);
            if(isset($id)){
                $uptitle = '添加顶级地区';
            }else{
                $uptitle = '上级地区为：'.$addr['name'];
            }
            $this->assign('uptitle',$uptitle);
            return $this->_form($this->table, 'form');
        }
    }
}