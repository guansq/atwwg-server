<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 14:35
 */

namespace app\spl\controller;

use app\spl\logic\Supportercenter;
use service\DataService;

class Suppliercenter extends Base{
    protected $table = 'SystemArea';
    protected $title = '供应商中心';
    const RISKLEVEL = [
        1 => '低',
        2 => '中',
        3 => '高',
    ];

    public function index(){
        $this->assign('title', $this->title);
        $sup_code = session('spl_user')['sup_code'];
        $logicSupInfo = Model('Supportercenter', 'logic');
        $sup_info = $logicSupInfo->getOneSupInfo($sup_code);//联合查询得到相关信息
        // dump($sup_info);
        // die();
        $supQuali = '';
        // 资质编码
        $qualilist = [
            'biz_lic' => '营业执照',
            'tax_reg_ctf' => '税务登记证',
            'org_code_ctf' => '组织机构代码证',
            'prd_ctf' => '生产许可证',
            'iso90001' => 'ISO90001',
            'ts_lic' => 'TS认证',
            'ped_lic' => 'PED',
            'api_lic' => 'API',
            'ce_lic' => 'CE',
            'sil_lic' => 'SIL',
            'bam_lic' => 'BAM',
            'other' => '其他'
        ];
        $supQualiList = [
            'biz_lic' => '',
            'tax_reg_ctf' => '',
            'org_code_ctf' => '',
            'prd_ctf' => '',
            'iso90001' => '',
            'ts_lic' => '',
            'ped_lic' => '',
            'api_lic' => '',
            'ce_lic' => '',
            'sil_lic' => '',
            'bam_lic' => '',
            'other' => ''
        ];
        $payStatus = ['' => '', 'uncheck' => '待审核', 'pass' => '审核通过', 'refuse' => '拒绝'];

        if($sup_info){
            if(empty($sup_info['pay_way_status'])){
                $sup_info['pay_way_status'] = '';
            }
            $sup_info['pay_way_status_name'] = $payStatus[$sup_info['pay_way_status']];
            $sup_info['found_date'] = atwDate($sup_info['found_date']);
            $sup_info['arv_rate'] = initPerVal($sup_info['arv_rate']);
            $sup_info['pass_rate'] = initPerVal($sup_info['pass_rate']);
            $sup_info['risk_level'] = key_exists($sup_info['risk_level'], self::RISKLEVEL) ? self::RISKLEVEL[$sup_info['risk_level']] : $sup_info['risk_level'];
            $this->assign('sup_info', $sup_info);
            $supQuali = $logicSupInfo->getSupQuali($sup_code);
            $statusList = ['' => '未审核', 'agree' => '同意', 'refuse' => '拒绝'];
            if(!empty($supQuali)){
                foreach($supQuali as $key => $iv){
                    if(empty($iv['status'])){
                        $iv['status'] = '';
                    }
                    // ,'status'=>$statusCheck[$iv['status']]
                    $supQualiList[$iv['code']] = array(
                        'term_start' => date('Y-m-d', $iv['term_start']),
                        'term_end' => date('Y-m-d', $iv['term_end']),
                        'img_src' => $iv['img_src'],
                        'status' => $iv['status'],
                        'remark' => $iv['remark'],
                        'statusStr' => $statusList[$iv['status']]
                    );
                }
            }
        }
        $imgInfos = explode(',', $sup_info['purch_contract']);
        $imgInfos = array_filter($imgInfos);
        $this->assign('supqualilist', $supQualiList);
        $this->assign('qualilist', $qualilist);
        $this->assign('imgInfos', $imgInfos);
        $this->assign('supquali', $supQuali);
        return view();
    }

    //更新支付方式
    public function updatePayStatus(){
        $data = input('param.');
        $logicSupInfo = Model('Supportercenter', 'logic');
        $sup_code = session('spl_user')['sup_code'];
        $payway = $data['payway'];
        $result = $logicSupInfo->updatepayway($sup_code, $payway);
        if($result){
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    //上传资格证书
    public function uploadSupInfo(){
        $now = time();
        $data = input('param.');
        $supLogic = Model('SupplierInfo', 'logic');
        $supQlflogic = Model('Supportercenter', 'logic');
        $sup_code = session('spl_user')['sup_code'];
        $begintime = strtotime($data['begintime']);
        $endtime = strtotime($data['endtime']);
        $code = $data['imgid'];
        $src = $data['img_src'];
        $qualilist = [
            'biz_lic' => '营业执照',
            'tax_reg_ctf' => '税务登记证',
            'org_code_ctf' => '组织机构代码证',
            'prd_ctf' => '生产许可证',
            'iso90001' => 'ISO90001',
            'ts_lic' => 'TS认证',
            'ped_lic' => 'PED',
            'api_lic' => 'API',
            'ce_lic' => 'CE',
            'sil_lic' => 'SIL',
            'bam_lic' => 'BAM',
            'other' => '其他'
        ];
        $queryQuali = $supQlflogic->querysupplierqualification($sup_code, $code);
        if(empty($queryQuali)){
            $sup_info = $supQlflogic->getOneSupInfo($sup_code);//联合查询得到相关信息
            //var_dump($sup_info);
            $result = DataService::save('supplier_qualification', [
                'code' => $code,
                'create_at' => time(),
                'update_at' => time(),
                'com_name' => $sup_info['name'],
                'sup_code' => $sup_code,
                'term_start' => $begintime,
                'term_end' => $endtime,
                'status' => '',
                'img_src' => $src,
                'name' => $qualilist[$code]
            ]);
        }else{
            $result = $supQlflogic->updatesupplierqualification($sup_code, $src, $code, $begintime, $endtime);
        }

        $sup = $supLogic->where('code', $sup_code)->find();
        $sup->qlf_check_count = model('SupplierQualification')
            ->where(['sup_code' => $sup_code,'status'=>''])
            ->count();
        //如果影响资质分  则重新计算
        if(in_array($code, Supportercenter::ADD_SCORE_QLF)){
            $qlfCount = $supQlflogic->where('code', 'IN', Supportercenter::ADD_SCORE_QLF)
                ->where('term_end', '>=', $now)
                ->where('status', 'agree')
                ->count();
            $sup->qlf_score = $qlfCount*5;
            $sup->tech_score = $sup->arv_rate*20 + $sup->pass_rate*60 + $qlfCount*5*0.4;
        }

        // 更新过期数量
        $sup->qlf_exceed_count = $supQlflogic
            ->where('sup_code' ,$sup_code)
            ->where('term_end' ,'<=', $now)
            ->count();
        $sup->update_at = $now;
        $sup->save();

        if($result){
            $where = [
                'code' => $sup_code
            ];
            //$logicSupInfo->addOneExceed($where);
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    //更新图片状态
    public function updateSupconfirmStatus(){
        $data = input('param.');
        $logicSupInfo = Model('Supportercenter', 'logic');
        $sup_code = session('spl_user')['sup_code'];
        $queryQuali = $logicSupInfo->querysupplierqualification($sup_code, $data['imgid']);
        if(empty($queryQuali)){
            return json(['code' => 4000, 'msg' => '请先上传图片再提交', 'data' => []]);
        }
        $detail = $logicSupInfo->updateSupconfirmStatus($sup_code, $data['imgid'], strtotime($data['begintime']), strtotime($data['endtime']));
        if($detail){
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    //添加图片
    public function add(){
        if(request()->isPost()){
            $data = input('param.');
            $code = $data['code'];
            $src = $data['src'];
            $sup_code = session('spl_user')['sup_code'];
            $logicSupInfo = Model('Supportercenter', 'logic');
            $qualilist = [
                'biz_lic' => '营业执照',
                'tax_reg_ctf' => '税务登记证',
                'org_code_ctf' => '组织机构代码证',
                'prd_ctf' => '生产许可证',
                'iso90001' => 'ISO90001',
                'ts_lic' => 'TS认证',
                'ped_lic' => 'PED',
                'api_lic' => 'API',
                'ce_lic' => 'CE',
                'sil_lic' => 'SIL',
                'bam_lic' => 'BAM',
                'other' => '其他'
            ];
            if($code == 'contract'){
                $result = $logicSupInfo->updatecontract($sup_code, $src);
            }else{
                $begintime = strtotime($data['begintime']);
                $endtime = strtotime($data['endtime']);
                $code = $data['code'];
                $queryQuali = $logicSupInfo->querysupplierqualification($sup_code, $code);
                if(empty($queryQuali)){
                    $sup_info = $logicSupInfo->getOneSupInfo($sup_code);//联合查询得到相关信息
                    //var_dump($sup_info);
                    $result = DataService::save('supplier_qualification', [
                        'code' => $code,
                        'create_at' => time(),
                        'update_at' => time(),
                        'com_name' => $sup_info['name'],
                        'sup_code' => $sup_code,
                        'term_start' => $begintime,
                        'term_end' => $endtime,
                        'status' => 'init',
                        'img_src' => $src,
                        'name' => $qualilist[$code]
                    ]);
                }else{
                    $result = $logicSupInfo->updatesupplierqualification($sup_code, $src, $code, $begintime, $endtime);
                }
            }
            if($result !== false){
                //$logicSupInfo->addOneExceed(['code'=>"$sup_code"]);
                $this->success('恭喜，保存成功哦！', '');
            }else{
                $this->error('保存失败，请稍候再试！');
            }
        }else{
            $code = input('code');
            $begintime = empty(input('begintime')) ? '' : input('begintime');
            $endtime = empty(input('endtime')) ? '' : input('endtime');
            $this->assign('code', $code);
            $this->assign('begintime', $begintime);
            $this->assign('endtime', $endtime);
            return view();
        }

    }
}