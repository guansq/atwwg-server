<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 9:47
 */

namespace app\spl\controller;
use service\LogService;
use think\Db;

class Login extends Base{

    /**
     * 控制器基础方法
     */
    public function _initialize() {
        if (session('spl_user') && $this->request->action() !== 'out') {
            $this->redirect('@spl');
        }
    }

    /**
     * 用户登录
     * @return string
     */
    public function index(){
        if ($this->request->isGet()) {
            $this->assign('title', '用户登录');
            return $this->fetch();
        } else {
            $username = $this->request->post('username', '', 'trim');
            $password = $this->request->post('password', '', 'trim');
            (empty($username) || strlen($username) < 4) && $this->error('登录账号长度不能少于4位有效字符!');
            (empty($password) || strlen($password) < 4) && $this->error('登录密码长度不能少于4位有效字符!');
            $user = Db::name('SystemUser')->where('user_name', $username)->find();
            empty($user) && $this->error('登录账号不存在，请重新输入!');
            $useLogic = model('BaseLogic','logic');
            $password = $useLogic->generatePwd($password,$user['salt']);
            //echo $password;die;
            ($user['password'] !== $password) && $this->error('登录密码与账号不匹配，请重新输入!');
            Db::name('SystemUser')->where('id', $user['id'])->update(['last_login_time' => ['exp', 'now()'], 'login_count' => ['exp', 'login_count+1']]);
            $user['sup_code'] = model('SupplierInfo')->getSupCodeBySupId($user['id']);//get sup_code
            session('spl_user', $user);
            $this->success('登录成功，正在进入系统...', '@spl');
        }
    }

    /**
     * 退出登录
     */
    public function out() {
        session('spl_user', null);
        session_destroy();
        $this->success('退出登录成功！', '@spl/login');
    }
}