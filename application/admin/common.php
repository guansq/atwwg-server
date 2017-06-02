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

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */

function auth($node) {
    return NodeService::checkAuthNode($node);
}

/*
 * 得到技术评分
 */
function getTechScore($code){
    return '80分';
}

/*
 * 供应商资质评分
 */
function getQualiScore($code){
    return '70分';
}

/*
 * 供应风险
 */
function getSupplyRisk($code){
    return '极小';
}
/*
 * 信用等级
 */
function getQualiLevel($code){
    return '优秀';
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