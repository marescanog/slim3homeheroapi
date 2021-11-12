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

    // checks if a phone number is already in the database
    public function is_in_DB(Request $request,Response $response)
    {
        $this->validator->validate($request,[
            "number"=>v::notEmpty()
         ]);

         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }

         $ModelResponse = $this->user->is_in_db(CustomRequestHandler::getParam($request,"number"));

         if(is_array($ModelResponse) && array_key_exists("success", $ModelResponse) && $ModelResponse["success"]){
            $responseMessage =  array(
                "data"=>$ModelResponse["data"],
                "message"=>"Fetch Request Successful",
            );
            $this->customResponse->is200Response($response,  $responseMessage);
        } else {
            $responseMessage =  array(
                "data"=>null,
                "message"=>"There was a problem with accessing the database",
            );
            $this->customResponse->is500Response($response, $responseMessage);
        }
    }

    // Gets All users from Database
    public function get_all_users(Request $request,Response $response)
    {
        $ModelResponse = $this->user->getAll();

        if(is_array($ModelResponse) && array_key_exists("success", $ModelResponse) && $ModelResponse["success"]){
            $responseMessage =  array(
                "data"=>$ModelResponse["data"],
                "message"=>"Fetch Request Successful",
            );
            $this->customResponse->is200Response($response,  $responseMessage);
        } else {
            $responseMessage =  array(
                "data"=>null,
                "message"=>"There was a problem with the PDO statement",
            );
            $this->customResponse->is500Response($response, $responseMessage);
        }

    }

        // Gets a single user from Database
        public function get_single_user(Request $request,Response $response, $args)
        {
            $ModelResponse = [];
            if (array_key_exists('id', $args)) {
                $ModelResponse = $this->user->getUserByID($args['id']);
            } else {
                $ModelResponse = $this->user->getUserByPhone($args['phone']);
            }
    
            if(is_array($ModelResponse) && array_key_exists("success", $ModelResponse) && $ModelResponse["success"] == true){
                // If user is not found
                if($ModelResponse["data"] == false){
                    $responseMessage =  array(
                        "message"=>"The User is not found.",
                        "isFound"=>false,
                        "success"=>true,
                        "data"=>null
                    );

                    return $this->customResponse->is404Response($response,  $responseMessage);
                }

                // If user is found
                $responseMessage =  array(
                    "data"=>$ModelResponse["data"],
                    "message"=>"Fetch Request Successful",
                    "isFound"=>true,
                    "success"=>true,
                );
                return $this->customResponse->is200Response($response,  $responseMessage);

            } else {
                $responseMessage =  array(
                    "data"=>$ModelResponse['data'],
                    "message"=>"There was a problem with UserController function the PDO statement",
                    "isFound"=>null,
                    "success"=>false,
                );
                $this->customResponse->is500Response($response, $responseMessage);
            }

            // $this->customResponse->is200Response($response,  "this route works ".$args['id']);
        }
}