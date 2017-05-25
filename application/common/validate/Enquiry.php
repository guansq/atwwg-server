<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/21
 * Time: 14:01
 */
namespace app\common\validate;
use think\Validate;

class Enquiry extends Validate{
    protected $rule = [
        'pr_code' => 'require',
        'item_code' => 'require',
    ];

    protected $message = [
        'pr_code.require' => '请传入请购单code',
        'item_code.require' => '请传入物料code',
    ];

}