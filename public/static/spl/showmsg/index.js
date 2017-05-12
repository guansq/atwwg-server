/**
 * 供应商管理首页
 * Created by Administrator on 2017/5/12.
 */

require(["echarts"],function(echarts){

  // 基于准备好的dom，初始化echarts实例
  var myChart = echarts.init(document.getElementById('main'));
  var myChart1 = echarts.init(document.getElementById('main1'));

  // 指定图表的配置项和数据
  var option = {
    //        标题
    title: {
    },
    //        提示框
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b} : {c}'+"%"
    },
    legend: {
    },
    xAxis: {
      type: 'category',
      name: '时间',
      splitLine: {show: false},
      data: ['2017-1', '2017-2', '2017-3', '2017-4', '2017-5', '2017-6', '2017-7', '2017-8', '2017-9']
    },
    grid: {
      left: '3%',
      right: '8%',
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
        data: [1, 5, 55,60, 100, 30,70,10,100]
      }

    ]
  };
  var option1 = {
    //        标题
    title: {
    },
    //        提示框
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b} : {c}'+"%"
    },
    legend: {
    },
    xAxis: {
      type: 'category',
      name: '时间',
      splitLine: {show: false},
      data: ['2017-1', '2017-2', '2017-3', '2017-4', '2017-5', '2017-6', '2017-7', '2017-8', '2017-9']
    },
    grid: {
      left: '3%',
      right: '8%',
      bottom: '3%',
      containLabel: true
    },
    yAxis: {
      type: 'value',
      name: '质量合格率(%)'
    },
    series: [
      {
        name: '质量合格率',
        type: 'line',
        data: [1, 5, 55,60, 100, 30,70,10,100]
      }

    ]
  };
  // 使用刚指定的配置项和数据显示图表。
  myChart.setOption(option);
  myChart1.setOption(option1);



})
