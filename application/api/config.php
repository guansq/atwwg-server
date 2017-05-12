<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:58
 */
return [
    'send_mail_url' => 'http://api.sendcloud.net/apiv2/mail/send',
    'send_template_mail_url' => 'http://api.sendcloud.net/apiv2/mail/sendtemplate',
    'from_mail' => '94600115@qq.com',
    'from_name' => '管管',
    'send_trigger_mail' =>
    [
        'use' => 'atwwg_single_sender_dev',
        'key' => 'ALArOxO9xe6KVlq6',
    ],
    'send_accord_mail' =>
    [
        'use' => 'atwwg_batch_sender_dev',
        'key' => 'arrIrMMSuLJMFWz2',
    ],
];