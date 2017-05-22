<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:55
 */

namespace app\api\controller;
use think\Request;

class Member extends BaseController{



    /**
     * @api {POST} /member/login 02.用户登录(todo)
     * @apiName login
     * @apiGroup member
     * @apiParam {String} account           账号/手机号/邮箱.
     * @apiParam {String} password          密码.
     * @apiParam {String} [wxOpenid]        微信openid.
     * @apiParam {String} [pushToken]       消息推送token.
     * @apiSuccess {String} accessToken     接口调用凭证.
     * @apiSuccess {String} refreshToken    刷新凭证.
     * @apiSuccess {Number} expireTime      有效期.
     */
    public function login(){
        //todo 校验参数
        assureNotEmpty();
        returnJson();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        returnJson();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        returnJson($id);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }



}