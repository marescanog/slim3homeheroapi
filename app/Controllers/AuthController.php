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

// This function will be soon depreciated and rewritten SINCE the SIM SMS Validation breaks up the phone number verification
// and password verification (Still in use because $app->post("/user-registration","AuthController:userRegister");) is still in use
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

    // This function will be soon depreciated and rewritten SINCE the SIM SMS Validation breaks up the phone number verification
// and password verification (Still in use because $app->post("/user-registration","AuthController:userRegister");) is still in use
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

    // Review function later
    // Saves a support agent user into the database
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


// REFACTORED & NEW CODE BELOW

// =============================
// WORKER
// @purpose adds a worker entry, hh_user entry and schedule entry into the database
// @accepts first_name, last_name, phone, pass (hashed)
// @returns 
public function workerCreateAccount(Request $request,Response $response){
    $this->customResponse->is200Response($response,  "this route works");
}

// =============================
// GLOBAL
    // This function accepts a phone number in body
    // returns success true if phone number is not in database
    // returns success false plus a 400 response object if phone number in database
    //          response object contains message "It looks like an account already exists with this phone number."
    //          response data contains 4 bools: isHomeowner, isWorker, isAdmin, isSupport
    //          if switch defaults, returns a 500 message "An error occured..."
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

            // return the roles that are assigned to this phone number
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

            }

        }
        
        $message = "It looks like an account already exists with this phone number.";
        $data = [];
        
        $data["isHomeowner"] = $isHomeowner;
        $data["isWorker"] = $isWorker;
        $data["isSupport"] = $isSupport;
        $data["isAdmin"] = $isAdmin;

        $responseMessage =  array(
            "data"=> $data,
            "message"=> $message,
        );

        return $this->customResponse->is400Response($response,  $responseMessage);
    }

// =============================
// GLOBAL
    // This function accepts password and confirm_password in body
    // returns success true if passwords plus a response object if passwords match and are not empty
    //          response object contains message "Secure password created"
    //          response data contains 1 string: the hashed password
    // returns success false plus a 400 response object if password and confirm_password are empty and do not match
    //          response object contains validation error messages
    //          response data contains validation errors
    public function userVerifyPass(Request $request,Response $response){
        // Server side validation using Respect Validation library
        // declare a group of rules ex. if empty, equal to etc.

        // Check if feilds are empy
        $this->validator->validate($request,[
            // Check if empty
            "password"=>v::notEmpty(),
            "confirm_password"=>v::notEmpty(),       
        ]);
        // Return error when empty
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // check if password has max and min length
        // check if confirm password matches password
        $this->validator->validate($request,[
            // Check if Max & Min length
            "password"=>v::Length(8, 30),
            // Check if Password Matches
            "confirm_password"=>v::equals(CustomRequestHandler::getParam($request,"password"))            
        ]);

        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $validationErrors = $this->validator->errors;
            if (array_key_exists("confirm_password", $validationErrors )) {
                $validationErrors["confirm_password"]["confirm_password"] = "Confirmation password must match entered password.";
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

        // return a hashed version of the password
        // Create Password Hash
        $hashed_pass = password_hash(CustomRequestHandler::getParam($request,"password"), PASSWORD_DEFAULT);

        $responseMessage =  array(
            "data"=> $hashed_pass,
            "message"=> "Secure password created",
        );

        return $this->customResponse->is200Response($response,  $responseMessage);
    }

// =============================
// GLOBAL
    // DUMMY ROUTE function
    // This function mimics the format for the MessageBird's Step 2: (Handling of SMS number) 
    // note, we have to convert the numbers to international format (+639...) no hyphen
    // This function does not generate an SMS, it is merely a dummy function for testing.
    public function generateSMSDummy(Request $request,Response $response){
        // Server side validation using Respect Validation library
        // declare a group of rules ex. if empty, equal to etc.

        // Check if feilds are empy
        $this->validator->validate($request,[
            // Check if empty
            "phone"=>v::notEmpty(),
            // Check if phone number is a phone number
            "phone"=>v::phone(),     
        ]);

        // Return error when empty or invalid
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // Clean up phone number to be +63 format

        // Create verify object

        // Make a request to Verify API
        // try catch block
        // if error return 400

        $obj = [];
        $obj['messagebird_id'] = 1;

        // if request is successful, return success message with id to signal to client to load SMS form
        $responseMessage =  array(
            "data"=> $obj,
            "message"=> "SMS Generated!",
        );

        return $this->customResponse->is200Response($response,  $responseMessage);
    }


// =============================
// GLOBAL
    // DUMMY ROUTE function
    // This function mimics the format for the MessageBird's Step 3: (Verify if token is correct) 
    // This function does not verify a generated SMS, it verifies static 123456 PIN
    // this is merely a dummy function for testing.
    // messagebird also requires an ID
    public function verifySMSDummy(Request $request,Response $response){
        // Server side validation using Respect Validation library
        // declare a group of rules ex. if empty, equal to etc.

        // Check if feilds are empty
        $this->validator->validate($request,[
            "messagebird_id"=>v::notEmpty(),
            "pin"=>v::notEmpty(),     
            // pin must be 6 digits
            "pin"=>v::length(6,6), 
        ]);

        // Return error when empty
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // Check if feilds are numeric
        $this->validator->validate($request,[
            "messagebird_id"=>v::number(),
            "pin"=>v::number(),    
        ]);

        // Return error when invalid
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // Make request to verify API
        // try catch
        // if error return 400
        // mimic message bird verification code with fake 123456 PIN
        $this->validator->validate($request,[
            "pin"=>v::equals("123456"), 
        ]);
        

        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $validationErrors = $this->validator->errors;
            if (array_key_exists("pin", $validationErrors )) {
                $validationErrors["pin"]["pin"] = "Incorrect PIN Code entered. Please try again.";
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

        // if request is successful, return success message with id to signal to client to load SMS form
        $responseMessage =  array(
            "data"=> null,
            "message"=> "SMS Verified!",
        );

         return $this->customResponse->is200Response($response,  $responseMessage);
    }
}