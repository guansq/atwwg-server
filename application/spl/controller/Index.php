<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 9:47
 */
namespace app\spl\controller;
use service\DataService;
use service\ToolsService;
use think\Db;
use think\View;
use controller\BasicSpl;

class Index extends Base{
    protected $title = '相关信息';

    public function index(){
        $list = Db::name('SystemUserMenu')->where('status', '1')->order('sort asc,id asc')->select();
        //dump(ToolsService::arr2tree($list));
        $menus = $this->_filterMenu(ToolsService::arr2tree($list));
        //dump(ToolsService::arr2tree($list));
        $this->assign('menus', $menus);
        $this->assign('title',$this->title);
        //dump(session('spl_user'));
        return view();
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus
     * @return array
     */
    private function _filterMenu($menus) {
        foreach ($menus as $key => &$menu) {
            if (!empty($menu['sub'])) {
                $menu['sub'] = $this->_filterMenu($menu['sub']);
            }
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (stripos($menu['url'], 'http') === 0) {
                continue;
            } elseif ($menu['url'] !== '#' && join('/', array_slice(explode('/', $menu['url']), 0, 3))) {
                $menu['url'] = url($menu['url']);
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }

    /**
     * 修改密码
     */
    public function pass() {
        /*if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }*/
        if (intval($this->request->request('id')) !== intval(session('spl_user.id'))) {
            $this->error('访问异常！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', true);
            return $this->_form('SystemUser', 'user/pass');
        } else {
            $data = $this->request->post();
            if ($data['password'] !== $data['repassword']) {
                $this->error('两次输入的密码不一致，请重新输入！');
            }
            $user = Db::name('SystemUser')->where('id', session('spl_user.id'))->find();
            if (md5($data['oldpassword']) !== $user['user_password']) {
                $this->error('旧密码验证失败，请重新输入！');
            }
            if (DataService::save('SystemUser', ['id' => session('spl_user.id'), 'user_password' => md5($data['password'])])) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 修改资料
     */
    public function info() {
        /*if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }*/

        if (intval($this->request->request('id')) === intval(session('spl_user.id'))) {
            return $this->_form('SystemUser', 'user/form');
        }
        $this->error('访问异常！');
    }
}