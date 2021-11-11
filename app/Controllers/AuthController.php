<?php


namespace App\Controllers;

use App\Models\User;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class AuthController
{

    protected  $customResponse;

    protected  $user;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->user = new User();

        $this->validator = new Validator();
    }

    // public function Register(Request $request,Response $response)
    // {
    //     $this->validator->validate($request,[
    //         "name"=>v::notEmpty(),
    //         "email"=>v::notEmpty()->email(),
    //         "password"=>v::notEmpty()
    //     ]);

    //     if($this->validator->failed())
    //     {
    //         $responseMessage = $this->validator->errors;
    //         return $this->customResponse->is400Response($response,$responseMessage);
    //     }

    //     if($this->EmailExist(CustomRequestHandler::getParam($request,"email")) )
    //     {
    //         $responseMessage = "this email already exists";
    //         return $this->customResponse->is400Response($response,$responseMessage);
    //     }

    //     $passwordHash = $this->hashPassword(CustomRequestHandler::getParam($request,"password"));

    //     $this->user->create([
    //        "name"=>CustomRequestHandler::getParam($request,"name"),
    //         "email"=>CustomRequestHandler::getParam($request,"email"),
    //         "password"=>$passwordHash
    //     ]);

    //     $responseMessage ="new user created successfully";

    //     $this->customResponse->is200Response($response,$responseMessage);

    // }


//     public  function hashPassword($password)
//   {
//     return password_hash($password,PASSWORD_DEFAULT);
//   }

//     public function EmailExist($email)
//     {
//     $count =  $this->user->where(["email"=>$email])->count();

//     if($count==0)
//     {
//         return false;
//     }
//     return true;
//     }


//     public function Login(Request $request, Response $response)
//     {
//        $this->validator->validate($request,[
//           "email"=>v::notEmpty()->email(),
//           "password"=>v::notEmpty()
//        ]);

//        if($this->validator->failed())
//        {
//            $responseMessage = $this->validator->errors;
//            return $this->customResponse->is400Response($response,$responseMessage);
//        }

//        $verifyAccount = $this->verifyAccount(CustomRequestHandler::getParam($request,"password"),
//                                                CustomRequestHandler::getParam($request,"email"));

//        if($verifyAccount==false)
//        {
//            $responseMessage ="invalid username or password";

//            return $this->customResponse->is400Response($response,$responseMessage);
//        }

//        $responseMessage = GenerateTokenController::generateToken(CustomRequestHandler::getParam($request,"email"));

//        return $this->customResponse->is200Response($response,$responseMessage);
//     }


//     public function verifyAccount($password,$email)
//     {
//         $hashPassword ="";
//         $count = $this->user->where(["email"=>$email])->count();

//         if($count==false)
//         {
//             return false;
//         }

//         $user = $this->user->where(["email"=>$email])->get();

//         foreach ($user as $users)
//         {
//             $hashPassword = $users->password;
//         }

//         $verify = password_verify($password,$hashPassword);

//         if($verify==false)
//         {
//             return false;
//         }

//         return true;
//     }

    public function userLogin(Request $request,Response $response){

        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "phone_number"=>v::notEmpty(),
            "password"=>v::notEmpty(),
            // Check if Max & Min length
            "phone_number"=>v::Length(1,18),
            "password"=>v::Length(8, 30),
            // Check if phone number is a phone number
            "phone_number"=>v::phone(),
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $verifyAccount = $this->verifyAccount(
            CustomRequestHandler::getParam($request,"password"), 
            CustomRequestHandler::getParam($request,"phone_number")
        );


        if(is_array($verifyAccount) 
            && array_key_exists("success", $verifyAccount) 
            && array_key_exists("message", $verifyAccount) 
            && $verifyAccount["success"] == false){
            $responseMessage =  array(
                "message"=>$verifyAccount["message"]
            );
            return $this->customResponse->is500Response($response, $responseMessage);
        } 

        if( $verifyAccount["data"] == false ){

            $responseMessage = "";

            if($verifyAccount["message"] == "The user is not found"){
                $responseMessage =  array(
                    "message"=>"This phone number is not associated with any account. Please check the input or sign-up to make a new account."
                );
                return $this->customResponse->is404Response($response, $responseMessage);
            }
    
            $responseMessage =  array(
                "message"=>"Wrong password, please check your input or click on 'forgot password' if you have trouble remembering."
            );
            return $this->customResponse->is400Response($response, $responseMessage);
        }
   
        $responseMessage =  array(
            "data"=>'JWT Token',
            "message"=>"Login Success"
        );

        return $this->customResponse->is200Response($response, $responseMessage);
    }


    // @name    verifies a user's account and phone number
    // @params  phone, password
    // @returns a Responsemessage object with the attributes "success", "data" and "message" or false on DB error
    //          sucess value is true when query is successfully run and false otherwise
    //          data value is true when user is found in the db and passwords match, false otherwise
    public function verifyAccount($password, $phone_number){

        // Check if user exists in the database via phone number
        $userObject = $this->user->getUserByPhone($phone_number);

        // Checks if getUser By Phone successfully runs
        if(is_array($userObject) 
            && array_key_exists("success", $userObject) 
            && array_key_exists("data", $userObject) 
            && $userObject["success"] == false){

            $responseMessage =  array(
                "message"=> $userObject['data'],
                "success"=>false,
                "data"=>null
            );

            return $responseMessage;
        }

        // check if the user is found
        if($userObject['data']== false){
            $responseMessage =  array(
                "message"=> "The user is not found",
                "success"=>true,
                "data"=>false
            );
            return $responseMessage;
        }

        // Check if passwords match
        $isPasswordMatch = password_verify($password , $userObject['data']['password']);

        if($isPasswordMatch == false){
            $responseMessage =  array(
                "message"=> "The entered password does not match",
                "success"=>true,
                "data"=>false
            );
            return $responseMessage;
        }

        $responseMessage =  array(
            "data"=>true,
            "message"=>"Verify function sucessfully ran",
            "success"=>true
        );

        return $responseMessage;
    }

}