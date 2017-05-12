/**
 * 订单性情页面
 * Created by Administrator on 2017/5/12.
 */

require(["laydate"],function(){
  $(".laydate-icon").focus(function(){
    /*
     laydate插件提供显示日期控件的方法
     laydate(options)
     * options - 选项,格式为 { key : value }
     * 选项
     * format - 日期格式
     默认格式为 YYYY-MM-DD hh:mm:ss(标准格式)
     * 客户端
     * 服务器端
     * 数据库
     * istime - 是否开启时间选择
     * 默认值为false,不开启
     * isclear - 是否显示清空按钮
     * istoday - 是否显示今天按钮
     * issure - 是否显示确认按钮
     */
    laydate({
      format : "YYYY年MM月DD日 hh:mm:ss",
      istime : true,
      isclear : true,
      istoday : true,
      issure : true
    });
  });
});


var $Check=$(".atw-orddet-tab tbody i");//单个选择按钮

var $Check_Shop=$(".atw-orddet-tab thead");//全选


$(function(){
  //店铺全选
  $Check_Shop.click(function () {
    var  part = $(this).find("i").attr("class");
    if(part =='select'){
      $(this).find("i").removeClass("select");
      $(this).next().find("i").removeClass("select");
    }else{
      $(this).find("i").addClass("select");
      $(this).next().find("i").addClass("select");
    }
  });
  //按钮选择
  $Check.click(function () {
    var  unitary = $(this).attr("class");
    if(unitary =='select'){
      $(this).removeClass("select");
      $(this).parents("tbody").prev().find("i").removeClass("select");
    }else{
      $(this).addClass("select");
      var length1 = $(this).parents("tbody").find("i").length;
      var length2 = $(this).parents("tbody").find(".select").length;
      if(length1==length2){
        $(this).parents("tbody").prev().find("i").addClass("select");
      }
    }
  });
});