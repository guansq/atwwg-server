<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 14:23
 */

namespace app\api\controller;

use think\Controller;
use think\Request;

class Push extends Base{
    public function index(){
        $client = new \JPush\Client($app_key, $master_secret);
    }
}