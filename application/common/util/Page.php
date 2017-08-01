<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/31
 * Time: 13:58
 */

namespace app\common\util;

class Page{

    public $pageIndex = 1;          //页码
    public $pageSize = 10;          //每页数据量
    public $itemTotal = 0;          //数据总数
    public $pageTotal = 0;          //分页总数
    public $itemStart = 0;          //数据开始的索引（从0开始）
    public $itemList = [];          //列表数据

    /**
     * Page constructor.
     */
    public function __construct($pageIndex = 1, $pageSize = 10){
        $this->pageIndex = $pageIndex;
        $this->pageSize = $pageSize;
        $this->itemStart = ($pageIndex - 1)*$pageSize;
    }

    public function setItemList($itemList){
        $this->itemList = $itemList;
    }

    public function getItemList($defaultItemList = []){
        if(empty($this->itemList)){
            return $defaultItemList;
        };
        return $this->itemList;
    }

    public function setItemTotal($itemTotal){
        $this->itemTotal = $itemTotal;
        $this->pageTotal = ceil($itemTotal/$this->pageSize);
    }

    public function getItemStart(){
        return $this->itemStart;
    }

}