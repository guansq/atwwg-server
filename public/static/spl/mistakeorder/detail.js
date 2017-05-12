/**
 * Created by Administrator on 2017/5/12.
 */

var $Check=$(".atw-orddet-tab tbody i");//单个选择按钮

var $Check_Shop=$(".atw-orddet-tab thead");//店铺全选


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