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


    // Saves a user into the database
    public function register_user(Request $request,Response $response)
    {
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "first_name"=>v::notEmpty(),
            "last_name"=>v::notEmpty(),
            "phone_number"=>v::notEmpty(),
            "password"=>v::notEmpty(),
            "confirm_password"=>v::notEmpty(),
            // Check if Max & Min length
            "phone_number"=>v::Length(1,18),
            "first_name"=>v::Length(1,50),
            "last_name"=>v::Length(1,50),
            "password"=>v::Length(8, 30),
            // Check if Password Matches
            "password"=>v::equals(CustomRequestHandler::getParam($request,"confirm_password")),
            // Check if phone number is a phone number
            "phone_number"=>v::phone(),
        ]);

        
        if($this->validator->failed())
        {
            $validationErrors = $this->validator->errors;
            if (array_key_exists("password", $validationErrors )) {
                $validationErrors["password"]["password"] = "Confirmation password must match entered password.";
            }

            $message = "";
            foreach ($validationErrors as $key => $value)
            {
                $message =  $message." ".$value[$key].".";
            }

            $responseMessage =  array(
                "data"=> $validationErrors,
                "message"=> $message,
            );

            return $this->customResponse->is400Response($response,$responseMessage);
        }


        $phone_number = CustomRequestHandler::getParam($request,"phone_number");
        $ModelResponse = $this->user->is_in_db($phone_number);
        // Check if phone number is in DB
        if(is_array($ModelResponse) && array_key_exists("data", $ModelResponse) && $ModelResponse["data"]){
            $responseMessage =  array(
                "data"=>null,
                "message"=>"Looks like there is an account associated with this number. Please login using this phone number or click on 'Forgot Password' if you do not remember your password.",
            );

            return $this->customResponse->is400Response($response,$responseMessage);
        }
        $ModelResponse = null;

        // Add Code here to sanitize number, whatever the user inputs it will always be saved as +639XXXXXXX format


        $ModelResponse = $this->user->register(
            CustomRequestHandler::getParam($request,"first_name"),
            CustomRequestHandler::getParam($request,"last_name"),
            $phone_number,
            CustomRequestHandler::getParam($request,"password")
        );

        if(is_array($ModelResponse) && array_key_exists("success", $ModelResponse) && $ModelResponse["success"]){
            $responseMessage =  array(
                "data"=>$ModelResponse,
                "message"=>"Registration Sucessful!",
            );
            $this->customResponse->is200Response($response, $responseMessage);
        } else {
            $responseMessage =  array(
                "data"=>$ModelResponse["data"],
                "message"=>"There was a problem with the PDO statement",
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
    
            if(is_array($ModelResponse) && array_key_exists("success", $ModelResponse) && $ModelResponse["success"]){
                // If user is not found
                if($ModelResponse["data"] == false){
                    $responseMessage =  array(
                        "message"=>"The User is not found."
                    );

                    return $this->customResponse->is404Response($response,  $responseMessage);
                }

                // If user is found
                $responseMessage =  array(
                    "data"=>$ModelResponse["data"],
                    "message"=>"Fetch Request Successful"
                );
                return $this->customResponse->is200Response($response,  $responseMessage);

            } else {
                $responseMessage =  array(
                    "data"=>$ModelResponse['data'],
                    "message"=>"There was a problem with UserController function the PDO statement",
                );
                $this->customResponse->is500Response($response, $responseMessage);
            }

            // $this->customResponse->is200Response($response,  "this route works ".$args['id']);
        }
}