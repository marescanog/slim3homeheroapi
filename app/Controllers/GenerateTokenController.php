<?php


namespace App\Controllers;



use App\Interfaces\SecretKeyInterface;
use \Firebase\JWT\JWT;
// use \Firebase\JWT\Key;

class GenerateTokenController implements SecretKeyInterface
{

    public static function generateToken($phone, $userType)
    {
        $now = time();
        $future = strtotime('+1 hour',$now);
        $secret = GenerateTokenController::JWT_SECRET_KEY;

        $payload = [
          "jti"=>$phone,
          "iat"=>$now,
          "exp"=>$future,
          "utype"=>$userType
        ];

        return JWT::encode($payload,$secret,"HS256");
    }

    public static function Authenticate($JWT,$Current_User_id)
    {
      try {
        $decoded = JWT::decode($JWT,GenerateTokenController::JWT_SECRET_KEY, array('HS256'));
        $payload = json_decode(json_encode($decoded),true);
      
        if($payload['utype'] == $Current_User_id) {
          $res=array("status"=>true);
        }else{
          $res=array("status"=>false,"Error"=>"Invalid Token or Token Exipred, So Please login Again!");
        }
      } catch (\UnexpectedValueException $e) {
        $res=array("status"=>false,"Error"=>$e->getMessage());
      }  catch (\DomainException $e){
        $res=array("status"=>false,"Error"=>$e->getMessage());
      } catch (\ExpiredException $e){
        $res=array("status"=>false,"Error"=>$e->getMessage());
      } catch (\Exception $e) {
        $res=array("status"=>false,"Error"=>$e->getMessage());
      }
      return $res;
    }
}