<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:55
 */

namespace app\api\controller;
use think\Controller;
use think\Request;


class Index extends Base{
    private $url = 'http://api.sendcloud.net/apiv2/mail/send';//普通发送
    private $templateurl = 'http://api.sendcloud.net/apiv2/mail/sendtemplate';

    function index(){
        //dump(config('send_trigger_mail'));
        //dump(config('send_accord_mail'));
        echo $this->send_tmp_mail();
    }

    /**
     * 发送邮件
     */
    function send_mail($to='tan3250204@sina.com',$subject='test',$html='来自管管的邮件，收到了吗') {

        $url = $this->url;
        $API_USER = config('send_trigger_mail')['use'];
        $API_KEY = config('send_trigger_mail')['key'];

        $param = array(
            'apiUser' => $API_USER, # 使用api_user和api_key进行验证
            'apiKey' => $API_KEY,
            'from' => config('from_mail'), # 发信人，用正确邮件地址替代
            'fromName' => config('from_name'),
            'to' => $to,# 收件人地址, 用正确邮件地址替代, 多个地址用';'分隔
            'subject' => $subject,
            'html' => $html,
            'respEmailId' => 'true'
        );


        $data = http_build_query($param);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
            ));
        $context  = stream_context_create($options);
        $result = file_get_contents($url, FILE_TEXT, $context);

        return $result;
    }

    function send_curl_mail($to='tan3250204@sina.com',$subject='test',$html='来自管管的邮件，收到了吗') {
        $API_USER = 'atwwg_single_sender_dev';
        $API_KEY = 'ALArOxO9xe6KVlq6';
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, $url = $this->url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'apiUser' => $API_USER, # 使用api_user和api_key进行验证
            'apiKey' => $API_KEY,
            'from' => '94600115@qq.com', # 发信人，用正确邮件地址替代
            'fromName' => '小管',
            'to' => '735969586@qq.com', # 收件人地址，用正确邮件地址替代，多个地址用';'分隔
            'subject' => '测试测试 TO 伍大美',
            'html' => "测试测试 TO 伍大美 测试测试 TO 伍大美")); #附件名称

        $result = curl_exec($ch);

        if($result === false) {
            echo curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    function send_tmp_mail() {
        $url = $this->templateurl;

        $vars = json_encode( array("to" => array('tan3250204@sina.com'),
                "sub" => array("%code%" => Array('123456'))
            )
        );

        $API_USER = 'atwwg_single_sender_dev';
        $API_KEY = 'ALArOxO9xe6KVlq6';
        $param = array(
            'apiUser' => $API_USER, # 使用api_user和api_key进行验证
            'apiKey' => $API_KEY,
            'from' => '94600115@qq.com', # 发信人，用正确邮件地址替代
            'fromName' => 'SendCloud',
            'xsmtpapi' => $vars,
            'templateInvokeName' => 'test12346',
            'subject' => 'Sendcloud php webapi template example',
            'respEmailId' => 'true'
        );


        $data = http_build_query($param);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
            ));
        $context  = stream_context_create($options);
        $result = file_get_contents($url, FILE_TEXT, $context);

        return $result;
    }

}