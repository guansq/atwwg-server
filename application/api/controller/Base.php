<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:51
 */
namespace app\api\controller;

use think\Controller;
use think\Request;

class Base extends Controller{
    function _initialize(){
        /*require_once EXTEND_PATH.'sendcloud/lib/util/HttpClient.php';
        require_once EXTEND_PATH.'sendcloud/lib/SendCloud.php';
        require_once EXTEND_PATH.'sendcloud/lib/util/Mail.php';
        require_once EXTEND_PATH.'sendcloud/lib/util/Mimetypes.php';*/
        //echo VENDOR_PATH;
    }
    function index(){
    }


}