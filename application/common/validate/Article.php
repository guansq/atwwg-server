<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/21
 * Time: 14:01
 */
namespace app\common\validate;
use think\Validate;

class Article extends Validate{
    protected $rules = [
        'title' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'title.require' => '标题必填',
        'content.require' => '内容必填',
    ];

}