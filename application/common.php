<?php

use service\DataService;
use think\Db;
use think\Validate;
use service\HttpService;

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

            // 调用第三方接口异常
            6000 => '调用第三方接口异常',

        ];

        if(empty($code)){
            return $CODE_MSG;
        }
        return $CODE_MSG[$code];
    }
}

// 接口返回json 数据
if(!function_exists('returnJson')){
    function returnJson($result = 0, $msg = '', $data = []){
        $ret = resultArray($result, $msg, $data);
        header('Content-type:application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        exit(json_encode($ret));
    }
}
// 返回数组
if(!function_exists('resultArray')){
    function resultArray($result = 0, $msg = '', $data = []){
        $code = $result;
        if(is_array($result)){
            $code = $result['code'];
            $msg = $result['msg'];
            $data = $result['result'];
        }
        if(empty($data)){
            $data = new stdClass();
        }
        $info = [
            'code' => $code,
            'msg' => empty($msg) ? getCodeMsg($code) : $msg,
            'result' => $data
        ];
        return $info;
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

/**
 * 生成訂單號
 */
if(!function_exists("generatOrderCode")){
    function generatOrderCode($prefix = ''){
        /* 选择一个随机的方案 */
        $randStr = date('YmdHis').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $prefix.$randStr;
    }

}


/**
 * 随机生成 $len 位字符
 */
function randomStr($len = 4){
    $chars_array = [
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
        "k", "l", "m", "n", "o", "p", "q", "r", "s", "t",
        "u", "v", "w", "x", "y", "z", "A", "B", "C", "D",
        "E", "F", "G", "H", "I", "J", "K", "L", "M", "N",
        "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X",
        "Y", "Z"
    ];
    $charsLen = count($chars_array) - 1;

    $outputstr = "";
    for($i = 0; $i < $len; $i++){
        $outputstr .= $chars_array[mt_rand(0, $charsLen)];
    }
    return $outputstr;
}

/**
 * 随机生成四位字符
 */
function randomNum($len = 4){
    $chars_array = [
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
    ];
    $charsLen = count($chars_array) - 1;

    $outputstr = "";
    for($i = 0; $i < $len; $i++){
        $outputstr .= $chars_array[mt_rand(0, $charsLen)];
    }
    return $outputstr;
}

if(!function_exists('validateData')){
    /**
     * Auther: WILL<314112362@qq.com>
     * Time: 2017-3-20 17:51:09
     * Describe: 校验参数是否有空值
     * @return bool
     */
    function validateData($params = [], $rule = []){
        if(empty($params)){
            returnJson(4001, '缺少必要参数.');
        }
        if(empty($rule)){
            foreach($params as $k => $v){
                $rule[$k] = 'require';
            }
        }
        $validate = new Validate($rule);
        if($validate->check($params)){
            return true;
        }
        returnJson(4002, '', $validate->getError());
    }
}


/*
 * PHPexcel读取并返回数组
 */
function format_excel2array11($excelObj,$filePath='',$sheet=0){
    if(empty($filePath) or !file_exists($filePath)){die('file not exists');}
    //$PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象
    $PHPReader = $excelObj;
    /*dump($PHPReader);
    if(!$PHPReader->canRead($filePath)){
        $PHPReader = new PHPExcel_Reader_Excel5();
        if(!$PHPReader->canRead($filePath)){
            echo 'no Excel';
            return ;
        }
    }*/
    $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
    $currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
    $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
    $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
    $data = array();
    for($rowIndex=1;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
        for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
            $addr = $colIndex.$rowIndex;
            $cell = $currentSheet->getCell($addr)->getValue();
            if($cell instanceof PHPExcel_RichText){ //富文本转换字符串
                $cell = $cell->__toString();
            }
            $data[$rowIndex][$colIndex] = $cell;
        }
    }
    return $data;
}

function prDates($start,$end){
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    while ($dt_start<=$dt_end){
        echo date('Y-m-d',$dt_start)."\n";
        $dt_start = strtotime('+1 day',$dt_start);
    }
}

/*
 * 得到技术评分 技术分=（a+b*20+c*60）/100 *40
 *

技术评分标准  分值

a  认证资质（ISO 5分；TS 5分；API 5分；PED 5分；）20

b  近半年交货及时率100%（数据来源U9，每周同步） 20

c  近半年质量合格率100%（数据来源U9，每周同步） 60
 */
function getTechScore($code){

    return '80分';
}

/*
 * 供应商资质评分  可随资质变化情况变更，资质有效期可显示，超过有效期的评分为0，需要提醒采购及时通知供方更新。
 */
function getQualiScore($code){
    return '70分';
}

/*
 * 供应风险
 */
/*单一资源&技术型：(独家采购)
a)	极高-供应商连续3次出现质量合格率＜99%*0.95，或交货及时率＜95%*0.95
b)	高-供应商连续2次出现质量合格率＜99%*0.95，或交货及时率＜95%*0.95
c)	低-除上述a、b条件以外。
充分竞争型：
a)	极高-同一物料供应商=2家，其中1家供应商质量合格率＜99%*0.95，或交货及时率＜95%*0.95
a)	高-同一物料供应商大于2小于4家，其中2家供应商质量合格率＜99%*0.95，或交货及时率＜95%*0.95
b)	低-同一物料供应商大于2小于4家，其中1家供应商质量合格率＜99%*0.95，或交货及时率＜95%*0.95*/

function getSupplyRisk($code){
    return '极小';
}
/*
 * 信用等级
 *
 *   信用等级	标准
 *   优	≥98分
 *   良	≥95分，＜98分
 *   一般	85分＜95分
 *   差	≤85分
 */
function getQualiLevel($score){
    if($score>=98){
        return '优';
    }
    if($score>=95){
        return '良';
    }
    if($score>=85){
        return '一般';
    }
    return '差';
}

/*
 * 时间的处理
 */
function atwDate($time){
    if(empty($time)){
        return $time;
    }
    return date('Y-m-d',$time);
}

/*
 * 金钱的处理-->统一后两位小数点
 */
function atwMoney($num,$ispre = true){
    $num = $num > 0 ? $num : 0;
    $formattedNum = number_format($num, 2);
    if($ispre){
        return '¥'.$formattedNum;
    }else{
        return $formattedNum;
    }
}

/*
 * 初始化百分比的值
 */
function initPerVal($num,$isMul = true,$ispre = ''){
    if($isMul){
        if($ispre == ''){
            return $num = empty($num) ? '' : (number_format($num*100, 2)).'%';
        }else{
            return $num = empty($num) ? '' : (number_format($num*100, 2));
        }
    }else{
        return $num = empty($num) ? '' : intval($num)/100;
    }
}

function keepdecimal($num){
    return number_format($num, 2);
}
/*
 * 发送信息
 */
function sendMsg($sendeeId,$title,$content,$type='single',$pri=3){
    $data = [
        'title' => $title,
        'content' => $content,
        'type' => $type,
        'publish_time' => time(),
        'pri' => $pri,
        'create_at' => time(),
        'update_at' => time()
    ];
    $msgId = model('Message')->saveMsg($data);
    $data = [
        'msg_id' => $msgId,
        'sendee_id' => $sendeeId,
        'create_at' => time(),
        'update_at' => time()
    ];
    $res = model('MessageSendee')->saveSendee($data);
    return $res;
}

/*
 * 通过当前日期得到最近12个月的初始月份
 */
function getReceDateArr($m){
    $mArr = [];
    $m = intval($m);
    $Y = date('Y');//当前的年
    for ($x=0; $x<12; $x++) {
        if($m > 0 ){
            $mArr[$x] = $Y.'-'.$m.'-01';
        }else{
            $mArr[$x] = 12 - abs($m);
            $PY = $Y - 1;//上一年
            $mArr[$x] = $PY.'-'.$mArr[$x].'-01';
        }
        $m = $m - 1;
    }
    return $mArr;
}
/*
 * 得到一个月的日期
 */
function getEndMonthTime($date){
    $start = strtotime($date);
    $times = 30*24*60*60;
    return $start + $times;
}
/*
 * 根据结束月份和开始月份得到月份区间
 */
function getMonthBetweenTime($smonth,$emonth){
    //得到各自的年
    $sYear = date('Y',$smonth);
    $eYear = date('Y',$emonth);
    $smon = intval(date('m',$smonth));
    $emon = intval(date('m',$emonth));
    $resArr = [];

    if($sYear == $eYear){
        $k = 0;
        for($i = $smon; $i <= $emon; $i++){
            $resArr[$k] = $sYear.'-'.$i.'-01';
            $k++;
        }
    }else if($sYear < $eYear){
        $grpYear = $eYear - $sYear;
        $months = $grpYear*12 - $smon + $emon;//总得月份数量
        //echo $months;die;
        $k = 0;
        for($i=0;$i<=$months;$i++){
            $curmon = ($smon + $i);//当前月份
            //echo $curmon;die;
            $curyear = intval(floor($curmon/12));//除以12取整  算出是否超出1年
            if($curyear > 0){
                $tmpmon = $curmon%12 == 0 ? 12 : $curmon%12;
                $resArr[$k] = $sYear+$curyear.'-'.$tmpmon.'-01';
            }else{
                $resArr[$k] = $sYear.'-'.$curmon.'-01';//起始年 和 当前月
            }
            $k++;
        }
    }
    return $resArr;
}



/**
 *数字金额转换成中文大写金额的函数
 *String Int $num 要转换的小写数字或小写字符串
 *return 大写字母
 *小数位为两位
 **/
function numbToCnYuan($num){
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    //精确到分后面就不要了，所以只留两个小数位
    $num = round($num, 2);
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "金额太大，请检查";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int)$num;
        //结束循环
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j-3;
            $slen = $slen-3;
        }
        $j = $j + 3;
    }
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    }else{
        return $c . "整";
    }
}


/**
 * 获取设备或配置系统参数
 * @param string $name  参数名称
 * @param bool   $value 默认值
 * @return string|bool
 */
function getSysconf($name, $defaultValue = ''){
    $logic = model('SystemConfig', 'logic');
    $data = $logic->where(['name' => $name])->find();
    if(empty($data)){
        return $defaultValue;
    }
    return $data['value'];

}

/**
 * 设置设备或配置系统参数
 * @param string $name 参数名称
 */
function setSysconf($name, $value = '', $group = 'app', $remark = ''){
    $data = ['name' => $name, 'value' => $value, 'group' => $group, 'remark' => $remark];
    $oldData = getSysconf($name);
    $logic = model('SystemConfig', 'logic');
    if(empty($oldData)){
        return $logic->create($data);
    }else{
        return $logic->update($data, ['name' => $name]);
    }

}

/*
 * 发送短信
 */
function sendSMS($phone,$content){
    $sendData = [
        'mobile' => $phone,
        'rt_appkey' => 'atw_wg',
        'text' => $content,
    ];
    HttpService::curl(getenv('APP_API_MSG').'SendSms/sendText',$sendData);//sendSms($data)
}

/*
 * 发送邮件
 */
function sendMail($to,$title,$content){
    $sendData = [
        'rt_appkey' => 'atw_wg',
        'fromName' => '安特威物供平台',//发送人名
        'to' => $to,
        'subject' => $title,
        'html' => $content,
        'from' => 'tan3250204@sina.com',//平台的邮件头
    ];
    HttpService::curl(getenv('APP_API_MSG').'SendEmail/sendHtml',$sendData);
}

/*
 * 推送信息
 */
function pushInfo($token,$title,$content){
    $sendData = [
        "platform" => "all",
        "rt_appkey" => "atw_wg",
        "alert" => $title,
        "regIds" => $token,
        //"platform" => "all",
        "androidNotification" => [
            "alert" => $title,
            "title" => $content,
            "builder_id" => "builder_id",
            "priority" => 0,
            "style" => 0,
            "alert_type" => -1,
            "extras" => [
                "0" => "RuiTu",
                "key" => "value"
            ]
        ]
    ];
    HttpService::curl(getenv('APP_API_MSG').'push',$sendData);
}

/*
 * 内部创建U9订单
 */
function placeOrder($itemInfo){

    $sendData = [];
    $sendData['DocDate'] = time();//单价日期
    $sendData['DocTypeCode'] = 'PO01';//单据类型
    $sendData['TCCode'] = 'C001';//币种编码
    $sendData['bizType'] = '316';//U9参数
    $sendData['isPriceIncludeTax'] = 1;//是否含税
    $sendData['supplierCode'] = $itemInfo['sup_code'];//供应商代码
    $lines = [];
    //foreach($itemInfo as $k => $v){}
    $lines[0] = [
        'ItemCode' => $itemInfo['item_code'],//料品号
        'OrderPriceTC' => $itemInfo['price'],//采购单价
        'OrderTotalTC' => $itemInfo['price']*$itemInfo['price_num'],//采购总金额
        'ReqQty' => $itemInfo['price_num'],//采购数量
        'RequireDate' => $itemInfo['req_date'],//请购时间-需求日期
        'SupConfirmDate' => $itemInfo['sup_confirm_date'],//供应商供货日期
        'TaxRate' => $itemInfo['tax_rate']*100,//税率
        'TradeUOM' => $itemInfo['tc_uom'],//交易单位
        'ValuationQty' => $itemInfo['tc_num'],//
        'ValuationUnit' => $itemInfo['price_uom'],//
        'srcDocPRLineNo' => $itemInfo['pr_ln'],
        'srcDocPRNo' => $itemInfo['pr_code']
    ];

    $sendData['lines'] = $lines;
    //dd($sendData);
    $httpRet = HttpService::curl(getenv('APP_API_U9').'index/po', $sendData);
    $res = json_decode($httpRet, true);//成功回写数据库
    //dump($res);
    return ['code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['result']];

}