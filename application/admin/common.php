<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 17:08
 */
use service\NodeService;
use service\DataService;
use think\Db;
use service\HttpService;
/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */

function auth($node) {
    return NodeService::checkAuthNode($node);
}

/*
 * 得到项目号
 */
function getProNo($pr_code){
    $prLogic = model('RequireOrder','logic');
    $where = [
        'pr_code' => $pr_code,
    ];
    return $prLogic->getProNo($where);
}

/*
 * 初始化百分比的值
 */
function initPerVal($num,$isMul = true){
    if($isMul){
        return $num = $num == '' ? '' : $num*100;
    }else{
        return $num = $num == '' ? '' : intval($num)/100;
    }
}

