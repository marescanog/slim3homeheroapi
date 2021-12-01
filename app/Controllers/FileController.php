<?php


namespace App\Controllers;

use App\Models\File;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class FileController
{
    protected  $customResponse;

    protected $file;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->file = new File();

        $this->validator = new Validator();
    }


    public function upload(Request $request,Response $response){

    
    
         return $this->customResponse->is200Response($response, "This route works" );

    }

    
    public function searchProj(Request $request,Response $response){

        $result = $this->file->searchProject("test");
    
         return $this->customResponse->is200Response($response, $result );
   }

//    ===========================


private function generateServerResponse($status, $message){
    $response = [];
    $response['status'] = 401;
    $response['message'] = $message;
    return $response;
}



   private function GET_USER_ID_FROM_TOKEN($bearer_token){
    // Extract token by omitting "Bearer"
    $jwt = substr(trim($bearer_token),9);

    // Decode token to get user ID
    $result =  GenerateTokenController::AuthenticateUserType($jwt, 1);
    //return $result ;
    if($result['status'] == false){
        return $this->generateServerResponse(401, $result['message']);
    }

    $userID =  $result["data"]["jti"];
    $userData = [];
    // return $this->customResponse->is401Response($response, $result );
    // Authenticate user
    // Get user information from DB and check if user is deleted
    // $result = $this->worker->is_deleted($userID);
    // $isDeleted = intval($result["data"]["is_deleted"]) != 0;
    // if($result["success"] == false){
    //     return $this->generateServerResponse(500, $result["data"]);
    // }
    // if($isDeleted){
    //     return $this->generateServerResponse(401, "The user is not available since it was deleted from the database.");
    // }
    return $userID;
}

   public function populateAddressForm(Request $request,Response $response){

        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        // GET USER DEFAULT ADDRESS
        $defaultHomeID = $this->file->getUserDefaultAddress($userID);
        // Error handling
        if($defaultHomeID['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $defaultHomeID['data']) );
        }

        // GET USER ALL ADDRESS
        $allAddress = $this->file->getUsersSavedAddresses($userID);
        // Error handling
        if(  $allAddress['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $allAddress['data']) );
        }

        // GET CITIES
        $cities = $this->file->getCities();
        // Error handling
        if($cities['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $cities['data']) );
        }

        // GET BARANGAY
        $barangay = $this->file->getBarangays();
        // Error handling
        if($barangay['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $barangay['data']) );
        }


        // GET HOMETYPE
        $hometype = $this->file->getHomeTypes();
        // Error handling
        if($hometype['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $hometype['data']) );
        }

        $formData = [];

        $formData["defaultHome"] = $defaultHomeID['data'];
        $formData["allAddress"] =   $allAddress['data'];
        $formData["cities"] = $cities['data'];
        $formData["barangays"] = $barangay['data'] ;
        $formData["hometype"] = $hometype['data'];

        // Return information needed for personal info page
       //return $this->customResponse->is200Response($response,  $userID );
        return $this->customResponse->is200Response($response, $formData);
   }



    










// Dec 1
   public function addAddress(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        //  Validate Data
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "street_no"=>v::notEmpty(),
            "street_name"=>v::notEmpty(),
            "barangay_id"=>v::notEmpty(),
            "home_type"=>v::notEmpty(),
        ]);

        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Get all necessary parameters
        $street_no = CustomRequestHandler::getParam($request,"street_no");
        $street_name = CustomRequestHandler::getParam($request,"street_name");
        $barangay_id = CustomRequestHandler::getParam($request,"barangay_id");
        $home_type = CustomRequestHandler::getParam($request,"home_type");
        $extra_address_info = CustomRequestHandler::getParam($request,"extra_address_info");

        // adds address
        $result = $this->file->saveAddress($userID, $street_no, $street_name,  $barangay_id,$home_type, $extra_address_info );
        // Error handling
        if(  $result['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $result['data']) );
        }

        // pulls up the ID and returns it
        $addedHome = $this->file->getLatestAddedHomeID($userID);
        // Error handling
        if(  $addedHome['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401,  $addedHome['data']) );
        }


        // Return information needed for add project
        return $this->customResponse->is200Response($response,  $addedHome);
        // return $this->customResponse->is200Response($response,  "This route works");
    }




















    public function addProject(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

                //  Validate Data
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "home_id"=>v::notEmpty(),
            "job_size_id"=>v::notEmpty(),
            "required_expertise_id"=>v::notEmpty(),
            "job_description"=>v::notEmpty(),
            "rate_offer"=>v::notEmpty(),
            "rate_type_id"=>v::notEmpty(),
            "is_exact_schedule"=>v::notEmpty(),
            "preferred_date_time"=>v::notEmpty(),
            "project_name"=>v::notEmpty(),
        ]);

        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Get all necessary parameters
        $home_id = CustomRequestHandler::getParam($request,"home_id");
        $job_size_id = CustomRequestHandler::getParam($request,"job_size_id");
        $required_expertise_id = CustomRequestHandler::getParam($request,"required_expertise_id");
        $job_description = CustomRequestHandler::getParam($request,"job_description");
        $rate_offer = CustomRequestHandler::getParam($request,"rate_offer");
        $rate_type_id = CustomRequestHandler::getParam($request,"rate_type_id");
        $preferred_date_time = CustomRequestHandler::getParam($request,"preferred_date_time");
        $project_name = CustomRequestHandler::getParam($request,"project_name");

        // adds address
        $result = $this->file->saveProject($userID,$home_id, $job_size_id, $required_expertise_id,$job_description, 
        $rate_offer,   $rate_type_id, $preferred_date_time, $preferred_date_time, $project_name);
        // Error handling
        if(  $result['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $result['data']) );
        }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,    $result );
        //return $this->customResponse->is200Response($response,  "This route works");
    }



    
    public function sdfsfsd(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }


// shkfjskdhfkjafk






        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $userID );
        // return $this->customResponse->is200Response($response,  "This route works");
    }
}