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

class Material extends BasicAdmin{
    protected $table = 'SystemArea';
    protected $title = '相关信息';

    public function index(){
        //echo '111111111';die;
        $this->assign('title',$this->title);
        return view();
    }

    public function del(){

    }

    public function add(){

    }

    public function edit(){
        return view();
    }
}