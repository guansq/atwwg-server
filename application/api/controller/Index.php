<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:55
 */

namespace app\api\controller;
use think\Request;

class Index extends BaseController{
    private $url = 'http://api.sendcloud.net/apiv2/mail/send';//普通发送
    private $templateurl = 'http://api.sendcloud.net/apiv2/mail/sendtemplate';

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        returnJson();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        returnJson($id);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }


    /**
     * 发送邮件
     */
    function send_mail($istrigger = true,$to='tan3250204@sina.com',$subject='test',$html='来自管管的邮件，收到了吗') {
        input('to');
        input('subject');
        input('html');
        $url = $this->url;
        if($istrigger){
            $API_USER = config('send_trigger_mail')['use'];
            $API_KEY = config('send_trigger_mail')['key'];
        }else{
            $API_USER = config('send_accord_mail')['use'];
            $API_KEY = config('send_accord_mail')['key'];
        }


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
            'templateInvokeName' => 'test_template_active',
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

    function send_sms() {
        $url = 'http://www.sendcloud.net/smsapi/send';

        $param = array(
            'smsUser' => '***',
            'templateId' => '1',
            'msgType' => '0',
            'phone' => '18068015721',
            'vars' => '{"%code%":"123456"}'
        );

        $sParamStr = "";
        ksort($param);
        foreach ($param as $sKey => $sValue) {
            $sParamStr .= $sKey . '=' . $sValue . '&';
        }

        $sParamStr = trim($sParamStr, '&');
        $smskey = '***';
        $sSignature = md5($smskey."&".$sParamStr."&".$smskey);


        $param = array(
            'smsUser' => '***',
            'templateId' => '1',
            'msgType' => '0',
            'phone' => '13412345678',
            'vars' => '{"%code%":"123456"}',
            'signature' => $sSignature
        );

        $data = http_build_query($param);
        echo $data;

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type:application/x-www-form-urlencoded',
                'content' => $data

            ));
        $context  = stream_context_create($options);
        $result = file_get_contents($url, FILE_TEXT, $context);

        return $result;
    }


}