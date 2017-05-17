<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 17:08
 */
use service\NodeService;

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */

function auth($node) {
    return NodeService::checkAuthNode($node);
}