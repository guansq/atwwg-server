<?php

use service\DataService;
use think\Db;
/**
 * 打印输出数据到文件
 * @param mixed       $data
 * @param bool        $replace
 * @param string|null $pathname
 */
function p($data, $replace = false, $pathname = NULL){
    is_null($pathname) && $pathname = RUNTIME_PATH.date('Ymd').'.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true))."\n";
    $replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}

/**
 * 获取微信操作对象
 * @param string $type
 * @return \Wechat\WechatReceive|\Wechat\WechatUser|\Wechat\WechatPay|\Wechat\WechatScript|\Wechat\WechatOauth|\Wechat\WechatMenu
 */
function & load_wechat($type = ''){
    static $wechat = array();
    $index = md5(strtolower($type));
    if(!isset($wechat[$index])){
        $config = [
            'token' => sysconf('wechat_token'),
            'appid' => sysconf('wechat_appid'),
            'appsecret' => sysconf('wechat_appsecret'),
            'encodingaeskey' => sysconf('wechat_encodingaeskey'),
            'mch_id' => sysconf('wechat_mch_id'),
            'partnerkey' => sysconf('wechat_partnerkey'),
            'ssl_cer' => sysconf('wechat_cert_cert'),
            'ssl_key' => sysconf('wechat_cert_key'),
            'cachepath' => CACHE_PATH.'wxpay'.DS,
        ];
        $wechat[$index] = Loader::get($type, $config);
    }
    return $wechat[$index];
}

/**
 * 安全URL编码
 * @param array|string $data
 * @return string
 */
function encode($data){
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(serialize($data)));
}

/**
 * 安全URL解码
 * @param string $string
 * @return string
 */
function decode($string){
    $data = str_replace(['-', '_'], ['+', '/'], $string);
    $mod4 = strlen($data)%4;
    !!$mod4 && $data .= substr('====', $mod4);
    return unserialize(base64_decode($data));
}


/**
 * 设备或配置系统参数
 * @param string $name  参数名称
 * @param bool   $value 默认是false为获取值，否则为更新
 * @return string|bool
 */
function sysconf($name, $value = false){
    static $config = [];
    if($value !== false){
        $config = [];
        $data = ['name' => $name, 'value' => $value];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if(empty($config)){
        foreach(Db::name('SystemConfig')->select() as $vo){
            $config[$vo['name']] = $vo['value'];
        }
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * array_column 函数兼容
 */
if(!function_exists("array_column")){

    function array_column(array &$rows, $column_key, $index_key = null){
        $data = [];
        foreach($rows as $row){
            if(empty($index_key)){
                $data[] = $row[$column_key];
            }else{
                $data[$row[$index_key]] = $row[$column_key];
            }
        }
        return $data;
    }

}

// 接口返回json 数据
if(!function_exists('getCodeMsg')){
    function getCodeMsg($code = 0){
        $CODE_MSG = [
            0 => '未知错误',
            2000 => 'SUCCESS',
            // 客户端异常
            4000 => '非法请求',
            4001 => '请求缺少参数',
            4002 => '请求参数格式错误',
            4003 => '请求参数格式错误',
            4004 => '请求的数据为空',
            // 客户端异常-用户鉴权
            4010 => '无权访问',
            4011 => 'token丢失',
            4012 => 'token无效',
            4013 => 'token过期',
            // 服务端端异常
            5000 => '服务端异常',
            5010 => '代码异常',
            5020 => '数据库操作异常',
            5030 => '文件操作异常',

        ];

        if(empty($code)){
            return $CODE_MSG;
        }
        return $CODE_MSG[$code];
    }
}

// 接口返回json 数据
if(!function_exists('returnJson')){
    function returnJson($code = 0, $msg = '', $data = []){
        if(empty($data)){
            $data = new stdClass();
        }
        $info['code'] = $code;
        $info['msg'] = empty($msg) ? getCodeMsg($code) : $msg;
        $info['result'] = $data;
        header('Content-type:application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        exit(json_encode($info));
    }
}

if(!function_exists('assureNotEmpty')){
    /**
     * Auther: WILL<314112362@qq.com>
     * Time: 2017-3-20 17:51:09
     * Describe: 校验参数是否有空值
     * @return bool
     */
    function assureNotEmpty($params = []){
        if(empty($params)){
            returnJson(4001, '缺少必要参数.');
        }
        foreach($params as $param){
            if(empty($param)){
                returnJson(4001, '缺少必要参数或者参数不合法.');
            }
        }
        return true;
    }
}

if(!function_exists("dd")){

    function dd($obj){
        var_dump($obj);
        die();
    }

}