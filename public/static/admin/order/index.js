/**
 * 订单管理
 * Created by Administrator on 2017/5/11.
 */

require(["jquery.dataTables","icheck"],function(){
  $(document).ready(function(){
    initPage();
    initEvent();
  });

  function initPage(){
    //icheck
    $('input').iCheck({
      checkboxClass: 'icheckbox_minimal',
      radioClass: 'iradio_minimal',
      increaseArea: '20%' // optional
    });

    // datatable
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
    } );
    // 立即同步ERP 点击事件
    $('#synchronization_btn').click( function () {
      alert( table.rows('.selected').data().length +' row(s) selected' );
    });
    //删除物料
    $('#remove_btn').click( function () {
      table.rows('.selected').remove().draw( false );
    } );
  }
  
  function initEvent(){
    // 点击详情
    $(".detail").click(function(e){
      // e.stopPropagation();
    });

    var checkAll =$('input.all');
    var checkboxs =$('input.check');

    checkAll.on('ifChecked ifUnchecked',function(event){
      if(event.type == 'ifChecked'){
        checkboxs.iCheck('check');
      }else{
        checkboxs.iCheck('uncheck');
      }
    });

    checkboxs.on('ifChanged',function(event){
      if(checkboxs.filter(':checked').length == checkboxs.length){
        checkAll.prop('checked',true);
      }else{
        checkAll.prop('checked',false);
      }
      checkAll.iCheck('update');
    })
  }
  
  



})

