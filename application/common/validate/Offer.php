<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 17:48
 */

namespace app\common\validate;
use think\Validate;

class Offer extends Validate{
    protected $rule = [
        'id' => 'require',
        'req_date' => 'require',
        'quote_price' => 'require|number',
    ];

    protected $message = [
        'id.require' => '请传入id',
        'req_date.require' => '可供货日期不能为空',
        'quote_price.require' => '单价不能为空',
        'quote_price.number' => '单价只能为数字',
    ];

}