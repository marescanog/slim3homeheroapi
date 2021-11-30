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

    public static function generateRegistrationToken($userID, $userType)
    {
        $now = time();
         $future = strtotime('+1 hour',$now);
        // $future = strtotime('+24 hour',$now);
        $secret = GenerateTokenController::JWT_SECRET_KEY;

        $payload = [
          "jti"=>$userID,
          "iat"=>$now,
          "exp"=>$future,
          "utype"=>$userType
        ];

        return JWT::encode($payload,$secret,"HS256");
    }

    // General for all tokens (Registration and User Token)
    public static function AuthenticateUserType($JWT,$user_type)
    {
      try {
        $decoded = JWT::decode($JWT,GenerateTokenController::JWT_SECRET_KEY, array('HS256'));
        $payload = json_decode(json_encode($decoded),true);

        if($payload['utype'] !== $user_type) {
          return $res=array("status"=>false,"message"=>"JWT - Err 1: Token unrecognized. Wrong User Type. Please sign into your account.");
        }

        // $token_userID = json_encode(intval($payload['jti']));
        // $user_id = json_encode(intval($user_id));
        // if($token_userID!==$user_id) {
        //   return $res=array("status"=>false,"message"=>"Token unrecognized. Wrong User ID.  Please sign into your account");
        // }

        $res=array("status"=>true,"message"=>"Token Recognized", "data"=>$payload);
        return   $res;

      } catch (\UnexpectedValueException $e) {
        $res=array("status"=>false,"message"=>"JWT - Ex 1:".$e->getMessage());
        return $res;
      }  catch (\DomainException $e){
        $res=array("status"=>false,"message"=>"JWT - Ex 2:".$e->getMessage());
        return $res;
      } catch (\ExpiredException $e){
        $res=array("status"=>false,"message"=>"JWT - Ex 3:".$e->getMessage());
        return $res;
      } catch (\Exception $e) {
        $res=array("status"=>false,"message"=>"JWT - Ex 4:".$e->getMessage());
        return $res;
      }

    }

    // ==== new for Global User (Homeowner, worker, support, admin)
    public static function generateUserToken($userID, $userType)
    {
        $now = time();
         $future = strtotime('+8 hour',$now);
        // $future = strtotime('+24 hour',$now);
        $secret = GenerateTokenController::JWT_SECRET_KEY;

        $payload = [
          "jti"=>$userID,
          "iat"=>$now,
          "exp"=>$future,
          "utype"=>$userType
        ];

        return JWT::encode($payload,$secret,"HS256");
    }

}