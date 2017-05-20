<?php
/**
 * 询价
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:55
 */

namespace app\api\controller;

use think\Request;

class Inquiry extends BaseController{



    /**
     * @api      {GET} /inquiry 01.询价单列表(todo)
     * @apiName  index
     * @apiGroup inquiry
     * @apiHeader {String} authorization-token           token.
     * @apiParam {String} status            状态值. init=未报价  quoted=已报价  winbid=中标 close=已关闭.
     *
     * @apiSUCCESS {Array} list            询价单号.
     * @apiSUCCESS {Number} list.id                  询价单id.
     * @apiSUCCESS {String} list.itemCode            料品编号.
     * @apiSUCCESS {String} list.itemName            料品名称.
     * @apiSUCCESS {String} list.priceUom            计价单位.
     * @apiSUCCESS {String} list.priceNum            计价数量.
     * @apiSUCCESS {String} list.subtotal            小计.
     * @apiSUCCESS {String} list.tcUom               交易单位.
     * @apiSUCCESS {String} list.reqDate             需求日期.
     * @apiSUCCESS {String} list.inqDate             询价日期.
     * @apiSUCCESS {String} list.quoteEndDate        报价截止日期.
     * @apiSUCCESS {String} list.status              状态值. init=未报价  quoted=已报价  winbid=中标 close=已关闭
     * @apiSUCCESS {String} list.statusStr           状态显示值. init=未报价  quoted=已报价  winbid=中标 close=已关闭
     * @apiSUCCESS {String} [list.promiseDate]       报价承诺交期.
     * @apiSUCCESS {String} [list.price]             报价单价.
     * @apiSUCCESS {String} [list.subTotal]          报价小计.
     * @apiSUCCESS {String} [list.remark]            报价备注.
     */
    public function index(){
        returnJson();
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(){
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request){
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id){
        returnJson($id);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id){
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int            $id
     * @return \think\Response
     */
    public function update(Request $request, $id){
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id){
        //
    }
    /**
     * @api      {POST} /inquiry/quote 08.报价(todo)
     * @apiName  quote
     * @apiGroup inquiry
     * @apiHeader {String} authorization-token           token.
     *
     * @apiParam {Number} id                  询价单id.
     * @apiParam {String} promiseDate         承诺交期.
     * @apiParam {String} price               询价单价.
     * @apiParam {String} remark              备注.
     */
    public function quote(){
        returnJson();
    }


}