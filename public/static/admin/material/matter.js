/**
 * 把物料管理页面拉到PHP代码处
 * Created by Administrator on 2017/5/11.
 */

require(["jquery.dataTables"],function(){

  $(document).ready(function() {
    initPage();
  } );
});

function initPage(){
  var table = $('#example').DataTable({
    //paging: false, 设置是否分页
    "info": false,  //去除左下角的信息
    "lengthChange": false, //是否允许用户改变表格每页显示的记录数
    "ordering": false, //是否允许Datatables开启排序
    "searching": false  //是否允许Datatables开启本地搜索
  });

  // tr点击选中事件
  $('#example tbody').on( 'click', 'tr', function () {
    $(this).toggleClass('selected');
  });

  // 立即同步ERP 点击事件
  $('#synchronization_btn').click( function () {
    alert( table.rows('.selected').data().length +' row(s) selected' );
  });
}


//弹出条形码弹框的点击事件
$("#example .barcode").click(function(e){
  e.stopPropagation();
  e.preventDefault();
  $(".barcode_box").css("display","block");
})

// 关闭条形码的弹出框
$(".close_box").click(function(){
  $(".barcode_box").css("display","none");
})

// 编辑的点击事件
$("#example .edit").click(function(e){
  // e.stopPropagation();
})

// 打印的点击事件
$("#stamp").click(function(){
  window.print();
})


