<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;

use think\Model;

class PoReceive extends Model{
    protected $table = '';


    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 记录 rcv
     * @param $params
     */
    function createReturnCode($params){
        $poLogic = model('Order', 'logic');
        $now = time();
        $saveData = [];
        $seq = $this->where('po_id',$params['id'])->order('seq','DESC')->group('seq')->count()+1;
        $rcvCode = 'RCV'.date('Ymd').sprintf('%04s', $seq);


        foreach($params['rcv'] as $piId => $pi){
            if(empty($pi['num'])){
                continue;
            }
            $saveData[] = [
                'po_id' => $params['id'],
                'rcv_code' => $rcvCode,
                'pi_id' => $piId,
                'seq' => $seq,
                'rcv_num' => $pi['num'],
                'heat_code' => $pi['heat_code'],
                'remark' => $pi['remark'],
                'create_at' => $now,
                'update_at' => $now,
            ];
        }
        if(empty($saveData)){
            return resultArray(4001);
        }
        if(!$this->saveAll($saveData)){
            return resultArray(5020);
        };
        return resultArray(2000, '', ['rcvCode'=>$rcvCode]);
    }
}