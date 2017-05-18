<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/18
 * Time: 16:24
 */
namespace app\common\validate;

use think\Validate;

class Banner extends Validate{
    protected $rule = [
        'name' => 'require',
        'src' => 'require',
    ];

    protected $message = [
        'name.require' => 'Banner名称必填',
        'src.require' => '请上传图片',
    ];
}