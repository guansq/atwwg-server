<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/18
 * Time: 16:17
 */
namespace app\admin\logic;
use app\common\model\SystemBanner as BannerModel;

class SystemBanner extends BaseLogic{

    public function getBannerList(){
        return BannerModel::getAllList();
    }
}
