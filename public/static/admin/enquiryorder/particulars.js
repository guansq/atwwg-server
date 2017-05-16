/**
 *
 * Created by Administrator on 2017/5/11.
 */

require(["icheck"],function(){
  $(document).ready(function(){
    initPage();
  });
});

function initPage(){
  $('input').iCheck({
    checkboxClass: 'icheckbox_minimal',
    radioClass: 'iradio_minimal',
    increaseArea: '20%' // optional
  });
}