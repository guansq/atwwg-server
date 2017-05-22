<?php
/**
 * User: Plator
 * Date: 2017/5/19
 * Time: 9:26
 * Desc: JWT加密使用
 */
namespace jwt;
use Firebase\JWT\JWT;
use think\Db;
class myJwt
{
    protected  $jwtKey = "ruit_antiwear2017";

    public function encodeToken ($data,$expire_time)
    {
        $data["last_login_time"] = $expire_time;
        $sign = tokenSign($data);
        $expire_time += time();
        $token = array
        (
            "data" => [$data["user_id"],$sign],
            "exp" => $expire_time,
        );
        return JWT::encode($token, $this -> jwtKey);
    }

    public function decodeToken ($jwt)
    {
        try{
            return  (Array)JWT::decode($jwt, $this -> jwtKey, array('HS256'));
        }catch(\Exception $e)
        {
            jsonReturn(4015,[],$e -> getMessage());
        }
    }

    public function checkToken ($jwt)
    {
        $decode = $this -> decodeToken($jwt);
        $decode["user_id"] = $decode["data"][0];
        $decode["sign"] =  $decode["data"][1];

        if(array_key_exists("user_id",$decode) && !empty($decode["user_id"]))
        {
            $userInfo = Db::table(config("database.prefix")."users") -> where("user_id = ". $decode["user_id"]) -> find();

            if(empty($userInfo))
                jsonReturn(4010);

           if($decode["sign"] == tokenSign($userInfo))
           {
                return $userInfo;
           }
            jsonReturn(4021);

        }else
        {
            jsonReturn(4016);
        }
    }
}

