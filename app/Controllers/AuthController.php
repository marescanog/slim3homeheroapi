<?php


namespace App\Controllers;

use App\Models\User;
use App\Models\Homeowner;
use App\Models\Support;
use App\Models\Worker;
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

    protected  $homeowner;

    protected  $support;

    protected  $worker;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->user = new User();

        $this->homeowner = new Homeowner();

        $this->support = new Support();

        $this->validator = new Validator();
    }

    public function userLogin(Request $request,Response $response){

        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "phone_number"=>v::notEmpty(),
            "password"=>v::notEmpty(),
            // Check if Max & Min length
            // "phone_number"=>v::Length(1,18),
            // Check if phone number is a phone number
            // "phone_number"=>v::phone(),
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

        if( $verifyAccount["isValid"] == false ){

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
   
        // GENERATE JWT TOKEN
        $userData = $verifyAccount['data'];
        $userData['token'] = GenerateTokenController::generateToken(CustomRequestHandler::getParam($request,"phone_number"));

        // $responseMessage =  array(
        //     "data"=> GenerateTokenController::generateToken(CustomRequestHandler::getParam($request,"phone_number")),
        //     "message"=>"Login Success"
        // );

        $responseMessage =  array(
            "data"=> $userData,
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

        $userData = [];
        $userData['first_name'] = $userObject['data']['first_name'];
        $userData['initials'] = substr($userObject['data']['first_name'], 0, 1).substr($userObject['data']['last_name'], 0, 1);
        $userData['initials'] = strtoupper($userData['initials']);

        $responseMessage =  array(
            "isValid"=>true,
            "data"=>$userData,
            "message"=>"Verify function sucessfully ran",
            "success"=>true
        );

        return $responseMessage;
    }

    // Saves a user into the database
    public function userRegister(Request $request,Response $response)
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


        $ModelResponse = $this->homeowner->createHomewner(
            CustomRequestHandler::getParam($request,"first_name"),
            CustomRequestHandler::getParam($request,"last_name"),
            $phone_number,
            CustomRequestHandler::getParam($request,"password")
        );

        if(is_array($ModelResponse) && array_key_exists("success", $ModelResponse) && $ModelResponse["success"]){
            $this->customResponse->is200Response($response, "Registration Sucessful!");
        } else {
            $responseMessage =  array(
                "data"=>$ModelResponse["data"],
                "message"=>"There was a problem with the PDO statement",
            );
            $this->customResponse->is500Response($response, $responseMessage);
        }
        
    }

    // Saves a user into the database
    public function supportRegister(Request $request,Response $response)
    {
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "first_name"=>v::notEmpty(),
            "last_name"=>v::notEmpty(),
            "phone_number"=>v::notEmpty(),
            "password"=>v::notEmpty(),
            "confirm_password"=>v::notEmpty(),
            "role"=>v::notEmpty(),
            "email"=>v::notEmpty(),
            // Check if Max & Min length
            "phone_number"=>v::Length(1,18),
            "first_name"=>v::Length(1,50),
            "last_name"=>v::Length(1,50),
            "password"=>v::Length(8, 30),
            // Check if Password Matches
            "password"=>v::equals(CustomRequestHandler::getParam($request,"confirm_password")),
            // Check if phone number is a phone number
            "phone_number"=>v::phone(),
            "email"=>v::email(),
            "role"=>v::intVal()->between(1, 6)
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


        $ModelResponse = $this->support->createSupportAgent(
            CustomRequestHandler::getParam($request,"first_name"),
            CustomRequestHandler::getParam($request,"last_name"),
            $phone_number,
            CustomRequestHandler::getParam($request,"password"),
            CustomRequestHandler::getParam($request,"email"),
            CustomRequestHandler::getParam($request,"role")
        );

        if(is_array($ModelResponse) && array_key_exists("success", $ModelResponse) && $ModelResponse["success"] == true){
            $this->customResponse->is200Response($response, "Support Agent Registration Successful!");
        } else {
            $responseMessage =  array(
                "data"=>$ModelResponse["data"],
                "message"=>"There was a problem with the PDO statement",
            );
            $this->customResponse->is500Response($response,  $ModelResponse);
        }
    }

    public function userPhoneCheck(Request $request,Response $response){
        // Server side validation using Respect Validation library
        // declare a group of rules ex. if empty, equal to etc.
        $this->validator->validate($request,[
            // Check if empty
            "phone"=>v::notEmpty(),
            // Check if phone number is a phone number
            "phone"=>v::phone(),
        ]);

        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // Check if user exists in the database via phone number
        // Get the user object associated with phone number
        $userObject = $this->user->getUserAccountsByPhone(CustomRequestHandler::getParam($request,"phone"));

        // If there is a user object, get the role associated with user
        // Otherwise return
        if($userObject["data"] == false){
            // there is no user associated with this phone number
            return $this->customResponse->is200Response($response,  "There is no user associated with this phone number");
        }


        // Check for user types associated with the phone number
        $isHomeowner = false;
        $isWorker = false;
        $isSupport = false;
        $isAdmin = false;
        $error = false;

        if(count($userObject["data"]) >= 1){
            // there is ONE or more roles associated with this phone number
            // return $this->customResponse->is200Response($response,  "There is one or more roles associated with this phone number");

            // check if the role/s is already a worker role
            for($x = 0; $x < count($userObject["data"]); $x++){
                switch($userObject["data"][$x]["user_type_id"]){
                    case 1:
                        $isHomeowner = true;
                        break;
                    case 2:
                        $isWorker = true;
                        break;
                    case 3:
                        $isSupport = true;
                        break;
                    case 4:
                        $isAdmin = true;
                        break;
                    default:
                        $error = true;
                    break;
                }

                if($error == true){
                    return $this->customResponse->is500Response($response,  "An error occured with the Athentication Controller. Please check the server issue.");
                }

                // if($isWorker == true){
                //     return $this->customResponse->is400Response($response,  "This user is a worker");
                // }
            }

        }
        
        // $message = "This user has the following roles: ";
        $message = "It looks like an account already exists with this phone number.";
        $data = [];
        
        // if($isHomeowner == true){
        //     $message = $message." Homeowner,";
        // }

        // if($isWorker == true){
        //     $message = $message." Worker,";
        // }
        
        // if($isSupport == true){
        //     $message = $message." Support,";
        // }

        // if($isAdmin == true){
        //     $message = $message." Admin,";
        // }

        $data["isHomeowner"] = $isHomeowner;
        $data["isWorker"] = $isWorker;
        $data["isSupport"] = $isSupport;
        $data["isAdmin"] = $isAdmin;

        $responseMessage =  array(
            "data"=> $data,
            "message"=> $message,
        );

        return $this->customResponse->is400Response($response,  $responseMessage);

        // return $this->customResponse->is200Response($response,  $userObject["data"]);
    }

}