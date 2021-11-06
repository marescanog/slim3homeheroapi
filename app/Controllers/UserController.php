<?php

namespace App\Controllers;

use App\Models\User;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use App\Db\DB;


class UserController
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

    public function is_in_DB(Request $request,Response $response)
    {
        $this->validator->validate($request,[
            "number"=>v::notEmpty()
         ]);
         // TODO Regex Validation for +63XXXXXXXXX and 09XXXXXXXXX numbers

         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }

        $result = $this->user->is_in_db(CustomRequestHandler::getParam($request,"number"));

        $responseMessage =  array(
                                "success"=>true,
                                "data"=>$result,
                                "message"=>"Data Succesfully Retreived",
                            );

        $this->customResponse->is200Response($response, $responseMessage);
    }

    public function register_user(Request $request,Response $response)
    {
        $this->validator->validate($request,[
            "first_name_yeah"=>v::notEmpty(),
            "last_name"=>v::notEmpty(),
            "phone_number"=>v::notEmpty(),
            "password"=>v::notEmpty(),
            "confirm_password"=>v::notEmpty(),
        ]);

        
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        //register($first_name, $last_name, $phone_number, $password)
        $isSuccess = $this->user->register(
            CustomRequestHandler::getParam($request,"first_name_yeah"),
            CustomRequestHandler::getParam($request,"last_name"),
            CustomRequestHandler::getParam($request,"phone_number"),
            CustomRequestHandler::getParam($request,"password")
        );

        if($isSuccess){
            $responseMessage =  array(
                "success"=>true,
                "data"=>$isSuccess,
                "message"=>"Registration Succesfull",
            );
        } else {
            $responseMessage =  array(
                "success"=>false,
                "data"=>$isSuccess,
                "message"=>"something went wrong",
            );
        }
        $this->customResponse->is200Response($response, $responseMessage);
    }

}