/**
 * 供应商
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
      format : "YYYY/MM/DD/ hh:mm:ss",
      istime : true,
      isclear : true,
      istoday : true,
      issure : true
    });
  });
})


$(function(){
  $(".atw-supplier-tab2 .center span").click(function(){
    $(".atw-supplier-tab2 .center span").removeClass("select");
    $(this).addClass("select");
    if( $(this).html()=="<i></i>三证合一"){
      $(".atw-supplier-tab2 .bottom .three").show().siblings().hide();
    }else{
      $(".atw-supplier-tab2 .bottom .one").show().siblings().hide();
    }
  });

});