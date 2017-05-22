<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 9:51
 */
namespace app\api\controller;

use service\ToolsService;
use think\Controller;
use jwt\myJwt;

class BaseController extends Controller{

    protected $getParams;
    protected $controller;
    protected $action;
    protected $user_id;

    public function _initialize ()
    {

        parent::_initialize();
        // CORS 跨域 Options 检测响应 允许跨域
        ToolsService::corsOptionsHandler();
        $this -> getParam ();
        $this->tokenGrantCheck();
    }



    /**
     * token检测
     */
    public function tokenGrantCheck ()
    {
        $called_class  = basename(get_called_class());
        //不需要token验证的控制器方法
        $except_controller =
            [
                "User" => ["login","reg","forget"]
            ];

        if(!array_key_exists($called_class,$except_controller) || !in_array($this->action,$except_controller[$called_class]))
        {
            if(array_key_exists("token",$this->getParams))
            {
                $user_info = (new myJwt) -> checkToken($this->getParams["token"]);
                $this->user_id = $user_info["user_id"];
            }else
            {
                returnJson(4011,[],"token");
            }
        }
    }

    /**
     * 获得get参数方法
     */
    protected function getParam ()
    {
        $this -> controller = request()->controller();
        $this -> action = request()->action();

        $this->getParams = array_filter(input("param."),function ($v){return $v != "";});

    }

    protected function encode ($data,$expire_time = "100000")
    {
        return  (new myJwt) -> encodeToken($data,$expire_time);
    }

    protected function checkLogin ($encodeData)
    {
        $this -> getParams["lal"];
        return  (new myJwt()) -> checkToken ($encodeData);
    }


    protected function flexClass ()
    {
        $class = new \ReflectionClass($this);
        $flex_methods = (array)$class->getMethods(\ReflectionMethod::IS_PUBLIC);
        array_walk($flex_methods,function ($v) use (&$methods){
            $current =(array)$v;
            if(trim($current["class"]) != __CLASS__ && trim($current["class"]) != "think\Controller" )
            {
                //                $methods[basename($current["class"])] = $current;
                $methods[basename($current["class"])][] = $current["name"];
            }
        });
        return $methods;
    }

    /**
     * 参数效验
     */
    protected  function paramCheck($guards)
    {
        if(array_key_exists($this -> action,$guards))
        {
            $paramDiff = implode(",",array_diff($guards[$this -> action],array_keys($this->getParams)));
            if($paramDiff)
            {
                jsonReturn(4011,$this->getParams,$paramDiff);
            }
        }

    }
}