<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\spl\logic;
use think\Model;

/**
 * 系统配置
 * Class Node
 * @package app\admin\model
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/14 18:12
 */
class SystemConfig extends Model{

    public function getAppConfig(){
        $config = [
            'lastApkUrl' => getSysconf('last_apk_url',''),
            'lastApkVersion' => getSysconf('last_apk_version',''),
            'lastApkVersionNum' => intval(getSysconf('last_apk_version_num','')),
            'defaultAvatar' => 'http://opmnz562z.bkt.clouddn.com/b22e33502ca2e6e1d93a996e062e8f2d.png',
            'payWays' => [
                '货到验收合格，票到120天付款',
                '货到验收合格，票到90天付款',
                '货到验收合格，票到30天付款',
                '预付30%，发货前付70%',
                '预付30%，货到现场30%，安装调试合格30%，一年质保金10%',
            ],
            'xx' => '其他参数',
        ];

        return resultArray(2000,'',$config);
    }

    public function getLastApk(){

        $lastApk = [
            'url' => getSysconf('last_apk_url',''),
            'version' => getSysconf('last_apk_version',''),
            'versionNum' => getSysconf('last_apk_version_num',''),
        ];

        return resultArray(2000,'',$lastApk);
    }

}
