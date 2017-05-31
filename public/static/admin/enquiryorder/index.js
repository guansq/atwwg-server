/**
 * 询价单
 * Created by Administrator on 2017/5/11.
 */

require(["laydate",'jquery.dataTables',"dataTables.bootstrap"],function(){
  $(document).ready(function(){
    initPage();
  })
});


function initPage(){

  initTimePicker();

  initTable();

}

function initTimePicker(){
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
      format : "YYYY年MM月DD日 hh:mm:ss",
      istime : true,
      isclear : true,
      istoday : true,
      issure : true
    });
  });
}

function initTable(){
  var table = $('#example').DataTable({
    //paging: false, 设置是否分页
    "info": false,  //去除左下角的信息
    "lengthChange": false, //是否允许用户改变表格每页显示的记录数
    "ordering": false, //是否允许Datatables开启排序
    "searching": false,  //是否允许Datatables开启本地搜索
    language: {
      "oPaginate": {
        "sFirst": "首页",
        "sPrevious": "上页",
        "sNext": "下页",
        "sLast": "末页"
      }
    },
    "processing": true,
    "serverSide": false,
    "ajax": {
      "url": '/Enquiryorder/getInquiryList',
      "type": "GET"
    },
    "pageLength": 20,
    "columns": [
      { "data": "io_code" },  //  <th>询价单号</th>
      { "data": "pr_code"},   //  <th>请购单号</th> },
      { "data": "item_code" },     //  <th>料号</th>
      //{ "data": "desc" },       //  <th>物料描述</th>
      //{ "data": "pro_no" },//  <th>项目号</th>
      { "data": "tc_uom" },//  <th>交易单位</th>,
      { "data": "price_uom" },//  <th>计价单位</th>
      { "data": "price_num" },//  <th>数量</th>
      { "data": "req_date" },//  <th>交期</th>
      { "data": "quote_date" },//  <th>询价日期</th>
      { "data": "quote_endtime" },//  <th>报价截止日期</th>

      { "data": "price_status" },   //  <th>报价状态</th>
      { "data": "status" },         //  <th>状态</th>
      { "data": "pur_attr" }        //  <th>详情</th>
    ]
  });
}

//待审批弹框点击事件
$(".approve").click(function(){
  $(".barcode_box").css("display","block");
});

//关闭弹框
$(".close_box").click(function(){
  $(".barcode_box").css("display","none");
});