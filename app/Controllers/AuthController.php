<?php


namespace App\Controllers;

use App\Models\User;
use App\Models\Homeowner;
use App\Models\Support;
use App\Models\Worker;
use App\Models\File;
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

    protected  $file;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->user = new User();

        $this->homeowner = new Homeowner();

        $this->support = new Support();

        $this->worker = new Worker();

        $this->file = new File();

        $this->validator = new Validator();
    }

// Homeowner
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
        $userData['token'] = GenerateTokenController::generateToken(CustomRequestHandler::getParam($request,"phone_number"),1);

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
// GLOBAL
// @purpose verifies any user by password and userID
// @accepts user_ID, password
// @returns obj (success, data) bool and on success another bool, on fail a message string
public function verifyUserByPasswordAndID($user_id, $password){
    $obj = [];
    // Get user object from database by ID
    $result = $this->user->getUserByID($user_id);

    // Check for PDO Errors
    if($result['success'] == false){
        return  $result;
    }

    // Check if user object exists (if not exist, error)
    if($result['data'] == false){
        $obj['success'] = true;
        $obj['data'] = "The user cannot be found";
        return $obj;
    }

    // Check if passwords match
    $isPasswordMatch = password_verify($password , $result['data']['password']);

    if($isPasswordMatch == false){
        $obj['success'] = true;
        $obj['data'] = "Incorrect Password"; 
        return $obj;
    }

    $obj['success'] = true;
    $obj['data'] = true;

    return $obj;

    return  $result;
}



// =============================
// WORKER
// @purpose verifies a worker by password and creates a registration token
// @accepts password, phone, user_ID
// @returns obj (JWT token, has_completed_registration) string, bool
public function createRegistrationToken(Request $request,Response $response){
    // Server side validation
    $this->validator->validate($request,[
        // Check if empty and valid
        "password"=>v::notEmpty(),
        "phone"=>v::notEmpty(),
        //"phone"=>v::phone(),
        "userID"=>v::notEmpty()
    ]);

    // Return Validation Errors
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        // 200 for the SWAl modal, errors on any other response
        return $this->customResponse->is200Response($response,$responseMessage);
    }

    // verify ID and password
    $result = $this->verifyUserByPasswordAndID(
        CustomRequestHandler::getParam($request,"userID"),
        CustomRequestHandler::getParam($request,"password") 
        );

    // return when wrong password
    if($result["data"] !== true){
        // 200 for the SWAl modal, errors on any other response
        return $this->customResponse->is200Response($response,$result["data"]);
    }

    // GENERATE JWT TOKEN
    $userData = [];
    // $userData['token'] = GenerateTokenController::generateToken(CustomRequestHandler::getParam($request,"phone"),2);
    // $userData['token'] = GenerateTokenController::generateRegistrationToken(CustomRequestHandler::getParam($request,"phone"),2);
    $userData['token'] = GenerateTokenController::generateRegistrationToken(CustomRequestHandler::getParam($request,"userID"),2);

    $userData['has_registered'] = false;
    
    $responseMessage =  array(
        "data"=> $userData,
        "message"=>"Registration Token Creation and verification Success"
    );

    return $this->customResponse->is200Response($response, $responseMessage);
}



// =============================
// WORKER
// @purpose checks the database to see if the user associated with a phone number has completed registration
// @accepts phone number
// @returns obj (user_id, phone_no, has_completed_registration) num, string, bool
public function hasWorkerRegistered(Request $request,Response $response){
    // Server side validation
    $this->validator->validate($request,[
        // Check if empty
        "phone"=>v::notEmpty(),
        // Check if numebr
        //"phone"=>v::phone()
    ]);

    // Return error message when validation failes
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    $responseMessage = $this->worker->isWorkerRegistered(CustomRequestHandler::getParam($request,"phone"));

    if($responseMessage['success'] == false){
        return $this->customResponse->is400Response($response,$responseMessage['data']);
    }

    if(count($responseMessage['data']) == 0){
        return $this->customResponse->is400Response($response,"No account is associated with this phone number");
    }

    $this->customResponse->is200Response($response,  $responseMessage['data'][0]);
}





// =============================
// WORKER
// @purpose adds a worker entry, hh_user entry and schedule entry into the database
// @accepts first_name, last_name, phone, pass (hashed)
// @returns registertoken
public function workerCreateAccount(Request $request,Response $response){
    $this->validator->validate($request,[
        // Check if empty, Max & Min length
        "first_name"=>v::notEmpty()->Length(2,50),
        "last_name"=>v::notEmpty()->Length(2,50),
        "phone"=>v::notEmpty()->Length(11, 18),
        "pass"=>v::notEmpty()->length(8,255),
    ]);

    // Returns a response when validator detects a rule breach
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    $users = $this->user->getUserAccountsByPhone(CustomRequestHandler::getParam($request,"phone"));

    if($users['data'] !== false){
        return $this->customResponse->is400Response($response,"Phone number already taken");
    }
    
    $responseMessage = $this->worker->createWorker(
        CustomRequestHandler::getParam($request,"first_name"),
        CustomRequestHandler::getParam($request,"last_name"),
        CustomRequestHandler::getParam($request,"phone"),
        CustomRequestHandler::getParam($request,"pass"));


    if($responseMessage['success'] == false){
        $this->customResponse->is500Response($response,  $responseMessage["data"]);
    }

        // GET USER acc by phone
        $usersWithPhone = $this->user->getUserAccountsByPhone(CustomRequestHandler::getParam($request,"phone"));

        // Get the user who has a user_type_id 2 for worker
        $usersWithPhone = $usersWithPhone['data'];
        $userID = "";
        if(count($usersWithPhone) == 1){
            $userID = $usersWithPhone[0]["user_id"];
        } else {
            for($x = 0; $x < count($usersWithPhone) && $userID == ""; $x++){
                if($usersWithPhone[$x]["user_type_id"] == 2){
                    $userID = $usersWithPhone[$x]["user_id"];
                }
            }
        }

        $generated_jwt = "";
        if($userID == ""){
            return $this->customResponse->is500Response($response, "Unable to generate a token session for registration. please refresh the page.");
        } else {
            // GENERATE JWT TOKEN
            $generated_jwt = GenerateTokenController::generateRegistrationToken($userID,2);
        }

        $userData = [];
        $userData['hasCompletedRegistration'] = false;
        $userData['token'] = $generated_jwt;

        if($generated_jwt == "" || $generated_jwt == null || empty($generated_jwt)){
            return $this->customResponse->is500Response($response, "Unable to generate a token session for registration. please refresh the page.");
        }

        $responseMessage =  array(
            "data"=> $userData,
            "message"=>"verification Success"
        );

        return $this->customResponse->is200Response($response, $responseMessage);
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
            //"phone"=>v::phone(),
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


// 
// ========================================================



// =============================
// HOMEOWNER
// @purpose adds a hoemowner entry, hh_user entry and schedule entry into the database
// @accepts first_name, last_name, phone, pass (hashed)
// @returns registertoken
public function homeownerCreateAccount(Request $request,Response $response){
    $this->validator->validate($request,[
        // Check if empty, Max & Min length
        "first_name"=>v::notEmpty()->Length(2,50),
        "last_name"=>v::notEmpty()->Length(2,50),
        "phone"=>v::notEmpty()->Length(11, 18),
        "pass"=>v::notEmpty()->length(8,255),
    ]);

    // Returns a response when validator detects a rule breach
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    $users = $this->user->getUserAccountsByPhone(CustomRequestHandler::getParam($request,"phone"));

    if($users['data'] !== false){
        return $this->customResponse->is400Response($response,"Phone number already taken");
    }

    $fname= CustomRequestHandler::getParam($request,"first_name");
    $lname= CustomRequestHandler::getParam($request,"last_name");

    $responseMessage = $this->homeowner->createHomeownerWithHashedPass(
        ucfirst($fname),
        ucfirst($lname),
        CustomRequestHandler::getParam($request,"phone"),
        CustomRequestHandler::getParam($request,"pass"));


    if($responseMessage['success'] == false){
        $this->customResponse->is500Response($response,  $responseMessage["data"]);
    }

        // GET USER acc by phone
        $usersWithPhone = $this->user->getUserAccountsByPhone(CustomRequestHandler::getParam($request,"phone"));

        // Get the user who has a user_type_id 1 for homeowner
        $usersWithPhone = $usersWithPhone['data'];
        $userID = "";
        if(count($usersWithPhone) == 1){
            $userID = $usersWithPhone[0]["user_id"];
        } else {
            for($x = 0; $x < count($usersWithPhone) && $userID == ""; $x++){
                if($usersWithPhone[$x]["user_type_id"] == 1){
                    $userID = $usersWithPhone[$x]["user_id"];
                    break;
                }
            }
        }

        $generated_jwt = "";
        if($userID == ""){
            return $this->customResponse->is500Response($response, "Unable to generate a token the login session. please refresh the page.");
        } else {
            // GENERATE JWT TOKEN
            $generated_jwt = GenerateTokenController::generateUserToken($userID,1);
        }

        $userData['token'] = $generated_jwt;
        $userData['status'] = 200;
        $userData['first_name'] = ucfirst( $fname);
        $userData['initials'] = substr( $fname, 0, 1).substr( $lname, 0, 1);
        $userData['role'] = null;
        $userData['email'] = null;

        if($generated_jwt == "" || $generated_jwt == null || empty($generated_jwt)){
            return $this->customResponse->is500Response($response, "Unable to generate a token the login session. please refresh the page.");
        }

        $responseMessage =  array(
            "data"=> $userData,
            "message"=>"verification Success"
        );

        return $this->customResponse->is200Response($response, $responseMessage);
}




// ============================= - FOR POSTMAN DEBUGGING
// WORKER ONLY - REGISTRATION
// @purpose verifies a worker by password and creates a registration token
// @accepts password, phone, user_ID
// @returns obj (JWT token, has_completed_registration) string, bool
public function createLoginToken(Request $request,Response $response){
    // Server side validation
    $this->validator->validate($request,[
        // Check if empty and valid
        "password"=>v::notEmpty(),
        "phone"=>v::notEmpty(),
        //"phone"=>v::phone(),
        "userID"=>v::notEmpty(),
        "userType"=>v::notEmpty()
    ]);

    // Return Validation Errors
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        // 200 for the SWAl modal, errors on any other response
        return $this->customResponse->is200Response($response,$responseMessage);
    }

    // verify ID and password
    $result = $this->verifyUserByPasswordAndID(
        CustomRequestHandler::getParam($request,"userID"),
        CustomRequestHandler::getParam($request,"password") 
        );

    // return when wrong password
    if($result["data"] !== true){
        // 200 for the SWAl modal, errors on any other response
        return $this->customResponse->is200Response($response,$result["data"]);
    }

    // GENERATE JWT TOKEN
    $userData = [];
    $userData['token'] = GenerateTokenController::generateUserToken(
        CustomRequestHandler::getParam($request,"userID"),
        CustomRequestHandler::getParam($request,"userType"));

    $userData['has_registered'] = false;
    
    $responseMessage =  array(
        "data"=> $userData,
        "message"=>"Login Token Creation and verification Success"
    );

    return $this->customResponse->is200Response($response, $responseMessage);
}

// =============================
// GLOBAL USER
// @purpose verifies a worker by password and creates a registration token
// @accepts password, phone, user_ID
// @returns obj (JWT token, has_completed_registration) string, bool
public function decodeLoginToken(Request $request,Response $response){
    // Server side validation
    $this->validator->validate($request,[
        // Check if empty and valid
        "token"=>v::notEmpty(),
        "userType"=>v::notEmpty()
    ]);

    // Return Validation Errors
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    // DECODE JWT TOKEN
    $result = GenerateTokenController::AuthenticateUserType(
        CustomRequestHandler::getParam($request,"token"),
        CustomRequestHandler::getParam($request,"userType"));

    if($result["status"] == false) {
        return $this->customResponse->is401Response($response, $result["message"] );
    }
    
    $responseMessage =  array(
        "data"=> $result,
        "message"=>"Login Token Creation and verification Success"
    );

    return $this->customResponse->is200Response($response, $responseMessage);
}


// =================================================================

    // =================================================================================================
    // == THIS ONE IS BEING USED BY THE HOMEOWNER LOGIN, using the global client login route
    // == Hurry mode: Re-review Later
    // =================================================================================================
    // This function if for all, it checks what type of user the user is
        // if correct user type, generates token.
        // if not correct type, returns a message with bools
    // it is referenced by the /general-schedule route
    // @param Request & Response, @returns formatted response object with status & message
    public function login(Request $request,Response $response){

        // Server side validation using Respect Validation library
        // declare a group of rules ex. if empty, equal to etc.
        $this->validator->validate($request,[
            // Check if empty
            "phone"=>v::notEmpty(),
            "password"=>v::notEmpty(),
            "userType"=>v::between(1, 4),
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
            $res = [];
            $res['status'] = 404;
            $res['message'] = "There is no user associated with this phone number";
            return $this->customResponse->is404Response($response,  $res);
        }

        // Check for user types associated with the phone number
        $isHomeowner = false;
        $isWorker = false;
        $isSupport = false;
        $isAdmin = false;
        $userData = null;

        // gather needed variables
        $usersList = $userObject['data'];
        $password = CustomRequestHandler::getParam($request,"password");
        $userType = CustomRequestHandler::getParam($request,"userType");
        $phone = CustomRequestHandler::getParam($request,"phone");

        // Check list if the userType indicated is one of the types
        for($x = 0; $x < count($usersList); $x++){
            // Check for single user
            switch($usersList[$x]["user_type_id"]){
                case "1":
                    $isHomeowner = true;
                    break;
                case "2":
                    $isWorker = true;
                    break;
                case "3":
                    $isSupport = true;
                    break;
                case "4":
                    $isAdmin = true;
                    break;
                default:
                    $error = true;
                break;
            }

            // Grab the needed user data from the list
            if( $userType == $usersList[$x]["user_type_id"] && $phone == $usersList[$x]["phone_no"]){
                $userData = $usersList[$x];
            }
        }

        $list_usertypes = ["Homeowner", "Worker", "Support", "Admin"];

        // if not found in list, the phone number has not registered as that user yet
        if($userData == null){
            $data = [];
            $data["status"] =  400;
            $data["isHomeowner"] =  $isHomeowner;
            $data["isWorker"] =  $isWorker ;
            $data["isSupport"] =  $isSupport;
            $data["isAdmin"] =  $isAdmin ;
            $data["message"] =  "The phone number is not associated with a ".$list_usertypes[$userType - 1]." account. Would you like to be redirected to the login portal for this user?";
            
            return $this->customResponse->is400Response($response,  $data);
        }
   
        // otherwise proceed with the login verification of password
            // Check if passwords match
            $isPasswordMatch = password_verify($password , $userData["password"]);
        
        if($isPasswordMatch == false){
            $res = [];
            $res['status'] = 401;
            $res['message'] = "Wrong password! Please check if you have entered the correct input.";
            return $this->customResponse->is404Response($response,  $res);
        }

        // GENERATE JWT TOKEN
        $generated_jwt = GenerateTokenController::generateUserToken($userData['user_id'], (int)$userType);

        if($generated_jwt == "" || $generated_jwt == null || empty($generated_jwt)){
            return $this->customResponse->is500Response($response, "Unable to generate a token the login session. please refresh the page.");
        }

        $resData['token'] = $generated_jwt;
        $resData['id'] = $userData['user_id']; //temporary addition by Wayne for session id for worker module
        $resData['status'] = 200;
        $resData['first_name'] = ucfirst($userData['first_name']);
        $resData['initials'] = substr($userData['first_name'], 0, 1).substr($userData['last_name'], 0, 1);
        $resData['role'] = null;
        $resData['email'] = null;
        $resData['profile_pic_location'] = false;

        if($userType == 3 || $userType == 4){
            // Get Data from Support Model
            // TODO
            $resData['role'] = null;
            $resData['email'] = null;
        }

        // GET PROFILE PICTURE
        $profPicResult = $this->file->getProfilePic($userData['user_id']);
        if(   $profPicResult['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,    $profPicResult['data']) );
        }

        if($profPicResult['data'] !=  false){
            $resData['profile_pic_location'] = $profPicResult['data']['file_path'];
        } 

        $responseMessage =  array(
            "data"=> $resData,
            "message"=>"verification Success"
        );

        return $this->customResponse->is200Response($response,  $resData);
    }



}