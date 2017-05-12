/**
 * Created by Administrator on 2017/5/12.
 */


/**
 *供应商质量合格率
 * Created by Administrator on 2017/5/12.
 */

require(["echarts","laydate"],function(echarts){

  var myChart = echarts.init(document.getElementById('report-echarts'));

  var option = {
    title: {
      text: '供应商交货及时率',
      left: 'center'
    },
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/> {b} : {c}'+"%"
    },
    legend: {
      left: 'left',
      data: [ '交货及时率']
    },
    xAxis: {
      type: 'category',
      name: '时间',
      splitLine: {show: false},
      data: ['2017-01', '2017-02', '2017-03', '2017-04', '2017-05', '2017-06', '2017-07', '2017-08', '2017-09']
    },
    grid: {
      left: '1%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    yAxis: {
      type: 'value',
      name: '交货及时率(%)'
    },
    series: [
      {
        name: '交货及时率',
        type: 'line',
        data: [3, 50, 36, 18, 68, 35,60,23,88]
      },
    ]
  };
  myChart.setOption(option);



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
});