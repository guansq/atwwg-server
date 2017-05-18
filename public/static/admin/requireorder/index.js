/**
 * 请购单管理
 * Created by Administrator on 2017/5/11.
 */

require(["icheck"],function(){

  $(document).ready(function(){
    initPage();
  });
});

function initPage(){
  //input
  $('input').iCheck({
    checkboxClass: 'icheckbox_minimal',
    radioClass: 'iradio_minimal',
    increaseArea: '20%' // optional
  });
}

// 打开弹框
$(".select_sell").click(function(e){
  e.preventDefault();
  $(".barcode_box").css("display","block");
});
// 关闭条形码的弹出框
$(".close_box").click(function(){
  $(".barcode_box").css("display","none");
});

