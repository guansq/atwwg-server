/**
 *
 * Created by Administrator on 2017/5/10.
 */

var orderTables;

require(["jquery.dataTables", "laydate"], function(){
  require(["dataTables.bootstrap"], function(){
    $(document).ready(function(){
      initPage();
    })
  });
});

function initPage(){

  initIoTable();

  //时间选择
  $(".date_time").focus(function(){
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
      format:"YYYY-MM-DD",
      istime:true,
      isclear:true,
      istoday:true,
      issure:true
    });
  });
}

/**
 * 初始化 待审核报价单列表
 */
function initIoTable(){
  //$.fn.dataTable.ext.errMode = 'none'; // 关闭报错信息
  orderTables = $('#need_ck_io_table').DataTable({
    //"paging": false, //设置是否分页
    "info":true,  //去除左下角的信息
    "lengthChange":false, //是否允许用户改变表格每页显示的记录数
    "ordering":false, //是否允许Datatables开启排序
    "searching":false,  //是否允许Datatables开启本地搜索
    "processing":true,
    //"serverSide": true,
    "ajax":{
      "url":'/showmsg/getUncheckIo',
      "type":"GET",
      "data":function(parameter){
        parameter.status = $("#status").val();
        parameter.quote_begintime = $("#quote_begintime").val();
        parameter.quote_endtime = $("#quote_endtime").val();
      }
    },
    "pageLength":10,
    language:{
      "oPaginate":{
        "sFirst":"首页",
        "sPrevious":"上页",
        "sNext":"下页",
        "sLast":"末页"
      },
      "info":"当前总记录数_TOTAL_条"
    },

    "columns":[
      {"data":"sup_name"},
      {"data":"item_code"},
      {"data":"item_name"},
      {"data":"pro_no"},
      {"data":"tc_num"},
      {"data":"price_uom"},
      {"data":"tc_uom"},
      {"data":"create_at_fmt"},
      {"data":"req_date_fmt"},
      {"data":"promise_date_fmt"},
      {"data":"quote_price"},
      {"data":"total_price"},
      {"data":"remark"},
      {
        "data":"id",
        render:function(data, type, row, meta){
          var $passBtn = $('<button><button/>');
          $passBtn.val(data);
          $passBtn.text('通过');
          $passBtn.addClass('check_btn_pass layui-btn layui-btn-small');

          var $refuseBtn = $('<button><button/>');
          $refuseBtn.val(data);
          $refuseBtn.text('拒绝');
          $refuseBtn.addClass('check_btn_refuse layui-btn layui-btn-small layui-btn-danger');
          return $passBtn.prop("outerHTML") + $refuseBtn.prop("outerHTML");
        }
      },
    ],
    "drawCallback":function(settings){
      $('.check_btn_pass').click(checkIoPass);
      $('.check_btn_refuse').click(checkIoRefuse);
    },
  });
}


/**
 * 审核 报价单
 */
function checkIoPass(){
  var ioId = this.value;
  $.msg.loading();
  if(!ioId){
    layer.alert('刷新后重试。');
    return false;
  }
  var url = "/Enquiryorder/placePurchOrderFromIo";
  var reqData = {
    'io_id':ioId
  };
  $.post(url, reqData, function(res){
    $.msg.close();
    if(res.code != 2000){
      layer.alert(res.msg);
    }
    orderTables.ajax.reload();
  });
}

/**
 * 审核 报价单
 */
function checkIoRefuse(){

  var ioId = this.value;
  $.msg.loading();
  if(!ioId){
    layer.alert('刷新后重试。');
    return false;
  }
  var url = "/Enquiryorder/refuseAndClear";
  var reqData = {
    'io_id':ioId
  };
  $.post(url, reqData, function(res){
    $.msg.close();
    if(res.code != 2000){
      layer.alert(res.msg);
    }
    orderTables.ajax.reload();
  });

}

