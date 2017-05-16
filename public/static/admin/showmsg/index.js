/**
 *
 * Created by Administrator on 2017/5/10.
 */
require(["jquery.dataTables","laydate"],function(){
  $(document).ready(function(){
    initPage();
  })
});

function initPage(){
  $('#example').DataTable({
    //paging: false, 设置是否分页
    "info": false,  //去除左下角的信息
    "lengthChange": false, //是否允许用户改变表格每页显示的记录数
    "ordering": false, //是否允许Datatables开启排序
    "searching": false  //是否允许Datatables开启本地搜索
  });

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
      format : "YYYY年MM月DD日 hh:mm:ss",
      istime : true,
      isclear : true,
      istoday : true,
      issure : true
    });
  });
}



