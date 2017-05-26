<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/18
 * Time: 15:15
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\DataService;
use think\Db;
use think\Validate;

class Banner extends BaseController{
    protected $table = 'SystemBanner';
    protected $title = 'banner';

    public function index(){
        $db = Db::name($this->table);
        # 列表排序默认处理
        if ($this->request->isPost() && $this->request->post('action') === 'resort') {
            $data = $this->request->post();
            unset($data['action']);
            foreach ($data as $key => &$value) {
                if (false === $db->where('id', intval(ltrim($key, '_')))->update(['sort' => $value])) {
                    $this->error('列表排序失败，请稍候再试！');
                }
            }
            $this->success('列表排序成功，正在刷新列表！', '');
        }
        $logic = model('SystemBanner', 'logic');
        //dump($logic->getBannerList());die;
        $this->assign('list',$logic->getBannerList());
        $this->assign('title',$this->title);
        return view();
    }

    public function add(){
        if(request()->isPost()){
            $data=input('param.');
            $result = $this->validate($data,'Banner');
            if($result !== true){
                $this->error($result);
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            LogService::write('Banner管理', '上传Banner成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            return view();
        }

    }

    /**
     * 删除用户
     */
    public function del() {

        if (DataService::update($this->table)) {
            $this->success("Banner删除成功！", '');
        }
        $this->error("Banner删除失败，请稍候再试！");
    }

}