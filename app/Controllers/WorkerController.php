<?php


namespace App\Controllers;

use App\Models\Worker;
use App\Models\User;
use App\Models\SupportTicket;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class WorkerController
{
    protected  $customResponse;

    protected $worker;

    protected $user;

    protected $supportticket;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->worker = new Worker();

        $this->user = new User();

        $this->supportticket = new SupportTicket();

        $this->validator = new Validator();
    }

    // =================================================================================================
    // This function <add your description>
    // it is referenced by <add your route>
    // @param Request & Response, @returns formatted response object with status & message
    public function template(Request $request,Response $response){
        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  "This Route Works");
    }

    // =================================================================================================
    // This function is a private function only used within this controller and NOT referenced by route or anything outside this file
        // @params accepts user_int(int), skilll_list(array of skill ids->project type table)
        // returns A an object containing sorted data of skills to add, update or delete
    private function processSkillsData($userID, $skill_list){
        // Logic to check whether we need to add, update, or delete skills based on received list
            // Get current saved skills list in DB that do not have a status of deleted
            $queryResult_getList_expertise = $this->worker->getList_expertise($userID);
            if($queryResult_getList_expertise["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $queryResult_getList_expertise["data"]) );
            }
            $current_active_skills_in_DB = $queryResult_getList_expertise["data"];
            // Convert to array
            $current_active_skills_arr = [];
            for($x = 0; $x < count($current_active_skills_in_DB); $x++){
                array_push($current_active_skills_arr, $current_active_skills_in_DB[$x]['id']);
            }

            // Initialize values to return
            $skills_toAdd = [];
            $skills_toDelete = [];
            $skills_toUpdate = [];

            // check if the skill already exists in the database
            // Get current saved skills list in DB including deleted
            $queryResult_getList_expertise_with_deleted = $this->worker->getList_expertise($userID, true);
            if($queryResult_getList_expertise_with_deleted["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $queryResult_getList_expertise_with_deleted["data"]) );
            }
            $current_skills_in_DB= $queryResult_getList_expertise_with_deleted["data"];
            // convert to array
            $current_skills_arr = [];
            for($x = 0; $x < count($current_skills_in_DB); $x++){
                array_push($current_skills_arr, $current_skills_in_DB[$x]['id']);
            }

            // get the skills to add to db vs the skills to update
            if(count($current_skills_in_DB) == 0){
                $skills_toAdd = $skill_list;
            } else {

                //check which skills are not in the db yet, if skill not in db add it otherwise update it
                for($x = 0; $x < count($skill_list); $x++){
                    if(in_array($skill_list[$x],$current_skills_arr) ){
                        // if the skill was already previously saved, no need to update. Otherwise add update.
                        if(!in_array($skill_list[$x],$current_active_skills_arr )){
                            array_push($skills_toUpdate, $skill_list[$x]);
                        }
                    } else {
                        array_push($skills_toAdd, $skill_list[$x]);
                    }
                }
            }

            // check for deleted skills
            for($x = 0; $x < count($current_active_skills_in_DB); $x++){
                if(!in_array($current_active_skills_in_DB[$x]['id'], $skill_list)){
                    array_push($skills_toDelete, $current_active_skills_in_DB[$x]['id']);
                }
            }

            $skills_data = [];
            $skills_data["skills_toAdd"] = $skills_toAdd;
            $skills_data["skills_toDelete"] = $skills_toDelete;
            $skills_data["skills_toUpdate"] = $skills_toUpdate;

            return  $skills_data;
    }

    // =================================================================================================
    // This function saves all the personal information needed for registration
    // it is references by the /save-personal-info route
    // @param Request & Response, @returns formatted response object with status & message
    public function save_personal_info(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Extract token by omitting "Bearer"
        $jwt = substr(trim($bearer_token),9);

        // Decode token to get user ID
        $result =  GenerateTokenController::AuthenticateUserType($jwt, 2);

        if($result['status'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $result['message']) );
        }

        $userID =  $result["data"]["jti"];

        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "skill_list"=>v::notEmpty(),
            "default_rate"=>v::notEmpty(),
            "default_rate_type"=>v::notEmpty(),
            "expiration_date"=>v::notEmpty(),
            "clearance_no"=>v::notEmpty(),
            "file_id"=>v::notEmpty(),
        ]);

        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Grab the nbi_file information from the body
        $file_id = CustomRequestHandler::getParam($request,"file_id");

        // file_id false means it is a new file, thus there is no id for it yet
        if($file_id == "false"){
            // Check if file information if empty only if it has a new file
            $this->validator->validate($request,[
                // Check if empty
                "file_name"=>v::notEmpty(),
                "file_path"=>v::notEmpty(),
                // "file_type"=>v::notEmpty()
            ]);

            // Return Validation Errors
            if($this->validator->failed())
            {
                $responseMessage = $this->validator->errors;
                return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
            }
        }

        // Second validation for values, ensure that the values are of the correct format and type, isarrry or number etc.
        $this->validator->validate($request,[
            // Check Values Validity
            "default_rate"=>v::floatVal(),
            "default_rate_type"=>v::intVal(),
            "expiration_date"=>v::date(),
        ]);
        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

    
        // ---------------
        // If data is good, then save all to db            
            // Grab the skill_list from the body (array of project_type ids-> which is the subcategory of expertise )
            $skill_list = CustomRequestHandler::getParam($request,"skill_list");
            // then process the raw data into something that our functions can use (used by the skills_QueryBuilder in our save personal information function)
            $skill_arr = explode(",",  $skill_list);
            $skill_data = $this->processSkillsData($userID, $skill_arr);
            // Check if skills list is a string
            if(is_string($skill_data)){
                return $this->customResponse->is500Response($response,$this->generateServerResponse(500, "Incorrect Data Format: Please check on the processing of skill list array into data object"));
            }
            // // For Debugging
            // return $this->customResponse->is500Response($response,$skill_data);

            //------
            // Grab the default rate & rate type from body // clean for 2's place decimal
            $default_rate = CustomRequestHandler::getParam($request,"default_rate");
            $default_rate = number_format($default_rate, 2, '.', '');
            $default_rate_type = CustomRequestHandler::getParam($request,"default_rate_type");

            //------
            // Grab the clearance no & expiration date from body
            $clearance_no = CustomRequestHandler::getParam($request,"clearance_no");
            $expiration_date = CustomRequestHandler::getParam($request,"expiration_date");
            // Grab the nbi file info
            $file_name = CustomRequestHandler::getParam($request,"file_name");
            $file_path = CustomRequestHandler::getParam($request,"file_path");
            // $file_type = CustomRequestHandler::getParam($request,"file_type");
            // For Deleting the old id
            $old_file_id = CustomRequestHandler::getParam($request,"old_file_id");

            // Add our collected and processed data into our custom function we wrote in the worker model
            $ModelResponse = $this->worker->save_personalInformation(
                $userID,  $skill_data, $default_rate, $default_rate_type, $clearance_no, $expiration_date,
                $file_id ,  $file_name,   $file_path,  $old_file_id
            );
        
            if($ModelResponse["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $ModelResponse["data"]) );
            }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response, $ModelResponse);

        //return $this->customResponse->is200Response($response,   $skill_data);
    }

    // =================================================================================================
    // This function is a private function only used within this controller and NOT referenced by route or anything outside this file
        // @params accepts status(int), message(string)
        // returns A formatted response object
    private function generateServerResponse($status, $message){
        $response = [];
        $response['status'] = 401;
        $response['message'] = $message;
        return $response;
    }

    // =================================================================================================
    // This function loads all the personal information needed for registration
    // it is references by the /personal-info route
    // @param Request & Response, @returns formatted response object with status & message
    public function getRegistration_personalInfo(Request $request,Response $response){
        // Get the bearer token from the Auth header (TODO, Use Private function GET_USER_ID_FROM_TOKEN) Refactor later...
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Extract token by omitting "Bearer"
        $jwt = substr(trim($bearer_token),9);

        // Decode token to get user ID
        $result =  GenerateTokenController::AuthenticateUserType($jwt, 2);

        if($result['status'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $result['message']) );
        }

        $userID =  $result["data"]["jti"];
        $userData = [];
        // return $this->customResponse->is401Response($response, $result );
        // Authenticate user
            // Get user information from DB and check if user is deleted
            $result = $this->worker->is_deleted($userID);
            $isDeleted = intval($result["data"]["is_deleted"]) != 0;
            if($result["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $result["data"]) );
            }
            if($isDeleted){
                return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "The user is not available since it was deleted from the database.") );
            }


        // Get user from the databse
            // Get expertise list
            $expertiseList = $this->worker->getList_expertise($userID);
            if($expertiseList["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $expertiseList["data"]) );
            }
            $userData["expertiseList"] = $expertiseList["data"];

            // Get rate and rate type
            $defaultRate = $this->worker->get_defaultRate_defaultRateType($userID);
            if($defaultRate["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $defaultRate["data"]) );
            }
            $userData["defaultRate_andType"] = $defaultRate["data"];

            // Get NBI information)
            $nbi_info = $this->worker->get_nbi_information($userID);
            if($nbi_info["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $nbi_info["data"]) );
            }
            $userData["nbi_information"] = $nbi_info["data"];

            // Get NBI file uploads
            $nbi_files = $this->worker->get_nbi_files($userID);
            if($nbi_files["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $nbi_files["data"]) );
            }
            $userData["nbi_files"] = $nbi_files["data"];


        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $userData);
    }
    
    /* LACKING: Code optimization for controller area */
    
    // =================================================================================================
    // This function loads all the job postings
    // it is referenced by /job-postings route
    // @param Request & Response, @returns formatted response object with status & message
    public function getJobPostings(Request $request,Response $response, array $args){

        $ModelResponse = $this->worker->getJobPostings($args['id']);
        
        return $this->customResponse->is200Response($response,  $ModelResponse);
    }

    // This function loads all the Ongoing Job Orders (Restrict by worker id/ only logged in workers postings)
    // it is referenced by /ongoing-job-orders route
    // @param Request & Response, @returns formatted response object with status & message
    public function getOngoingJobOrders(Request $request,Response $response, array $args){

        $ModelResponse = $this->worker->getOngoingJobOrders($args['id']);
        
        return $this->customResponse->is200Response($response,  $ModelResponse);
    }

    // This function loads all the Past Job Orders (Restrict by worker id/ only logged in workers postings & isCompleted)
    // it is referenced by /past-job-orders route
    // @param Request & Response (with get parameter indicating if cancelled job orders should be included), 
    // @returns formatted response object with status & message
    public function getPastJobOrders(Request $request,Response $response, array $args){


        $ModelResponse = $this->worker->getPastJobOrders($args['id'],
            CustomRequestHandler::getParam($request,"includeCancelled")
        );
        
        return $this->customResponse->is200Response($response,  $ModelResponse);
    }

    // This function loads all the Past Job Orders (Restrict by worker id/ only logged in workers postings & isCompleted)
    // it is referenced by /past-job-orders route
    // @param Request & Response @returns formatted response object with status & message
    public function getReviews(Request $request,Response $response, array $args){


        $ModelResponse = $this->worker->getReviews($args['id']);
        
        return $this->customResponse->is200Response($response,  $ModelResponse);
    }

    // 6. PUT Update NBI Info - Will be handled by Worker registration Route (So just use reuse the route used in registration)
    // 7. POST add Licesce & Certificate

    // 8. PUT/POST add Introduction
    // This function updates the introduction field of the worker
    // it is referenced by /add-introduction route
    // @param Request & Response @returns formatted response object with status & message
    public function addIntroduction(Request $request,Response $response, array $args){


        $ModelResponse = $this->worker->addIntroduction($args['id']);
        
        return $this->customResponse->is200Response($response,  $ModelResponse);
    }
    // 9. PUT update information - uses a combination of functions from models
    // 10. PUT save featured projects
    // 11. PUT/POST add project photos (two routes are needed, one route is saving to the google cloud storage- currently there's only 1 route for save one photo and not multiple photos. The multiple photos is still pending, the other route will be your code to save information to DB)
    // 12. Get Worker Info  - For the account profile page, feel free to use the DB functions in the model or write your own
    // 13. Get/Save Services offered from DB  - Worker ( refer to Project type table and not expertise)

// ===========================================================================================================
// ===========================================================================================================
// ===========================================================================================================



    // == Hurry mode: Re-review Later - Nov 28
    // =================================================================================================
    // This function Revceives a Bearer token and Decodes it to return a USER ID
    // Domain is only within this file
    public function GET_USER_ID_FROM_TOKEN($bearer_token){ //changed by Wayne for direct id retrieval for worker module
        // Extract token by omitting "Bearer"
        $jwt = substr(trim($bearer_token),9);

        // Decode token to get user ID
        $result =  GenerateTokenController::AuthenticateUserType($jwt, 2);
        //return $result ;
        if($result['status'] == false){
            return $this->generateServerResponse(401, $result['message']);
        }

        $userID =  $result["data"]["jti"];
        $userData = [];
        // return $this->customResponse->is401Response($response, $result );
        // Authenticate user
        // Get user information from DB and check if user is deleted
        $result = $this->worker->is_deleted($userID);
        $isDeleted = intval($result["data"]["is_deleted"]) != 0;
        if($result["success"] == false){
            return $this->generateServerResponse(500, $result["data"]);
        }
        if($isDeleted){
            return $this->generateServerResponse(401, "The user is not available since it was deleted from the database.");
        }
        return $userID;
    }

    // == Hurry mode: Re-review Later - Nov 28
    // =================================================================================================
    // This function loads if the worker has a schedule preference (Only general)
    // it is referenced by the /general-schedule route
    // @param Request & Response, @returns formatted response object with status & message
    public function get_general_schedule(Request $request,Response $response){
         // Get the bearer token from the Auth header
         $bearer_token = JSON_encode($request->getHeader("Authorization"));

         // Catch the response, on success it is an ID, on fail it has status and message
         $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);
 
         // Error handling
         if(is_array( $userID) && array_key_exists("status", $userID)){
             return $this->customResponse->is401Response($response, $userID);
         }
 
         // GET VALUES FROM DB
         $result = $this->worker->get_save_worker_schedule_preference($userID);
         if($result['success'] == false){
             return $this->customResponse->is500Response($response, $result['data']);
         }

         $data = $result['data'];

         $formattedData = [];
         $formattedData["id"] = $data["id"];
         $formattedData["has_schedule_preference"] = $data["has_schedule_preference"];

         $scheduleData = [];
         $scheduleData["id"] = $data["id"];
         $scheduleData["is_monday_off"] = $data["is_monday_off"];
         $scheduleData["monday_start_time"] = $data["monday_start_time"];
         $scheduleData["monday_end_time"] = $data["monday_end_time"];
         $scheduleData["is_tuesday_off"] = $data["is_tuesday_off"];
         $scheduleData["tuesday_start_time"] = $data["tuesday_start_time"];
         $scheduleData["tuesday_end_time"] = $data["tuesday_end_time"];
         $scheduleData["is_wednesday_off"] = $data["is_wednesday_off"];
         $scheduleData["wednesday_start_time"] = $data["wednesday_start_time"];
         $scheduleData["wednesday_end_time"] = $data["wednesday_end_time"];
         $scheduleData["is_thursday_off"] = $data["is_thursday_off"];
         $scheduleData["thursday_start_time"] = $data["thursday_start_time"];
         $scheduleData["thursday_end_time"] = $data["thursday_end_time"];
         $scheduleData["is_friday_off"] = $data["is_friday_off"];
         $scheduleData["friday_start_time"] = $data["friday_start_time"];
         $scheduleData["friday_end_time"] = $data["friday_end_time"];
         $scheduleData["is_saturday_off"] = $data["is_saturday_off"];
         $scheduleData["saturday_start_time"] = $data["saturday_start_time"];
         $scheduleData["saturday_end_time"] = $data["saturday_end_time"];
         $scheduleData["is_sunday_off"] = $data["is_sunday_off"];
         $scheduleData["sunday_start_time"] = $data["sunday_start_time"];
         $scheduleData["sunday_end_time"] = $data["sunday_end_time"];

         $formattedData["schedule_data"] = $scheduleData;

         $lead_notice_time_data = [];
         $lead_notice_time_data["notice_time"] = $data["notice_time"]; 
         $lead_notice_time_data["lead_time"] = $data["lead_time"]; 

         $formattedData["lead_notice_time_data"] =  $lead_notice_time_data ;
 
         // Return information needed for personal info page
         return $this->customResponse->is200Response($response, $formattedData  );
    }

    // == Hurry mode: Re-review Later - Nov 28
    // =================================================================================================
    // This function SAVES if the worker has a schedule preference (Only general)
    // it is referenced by the /general-schedule route
    // @param Request & Response, @returns formatted response object with status & message
    public function save_general_schedule(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        // VALIDATE VALUES RECEIVED FROM USER
        $this->validator->validate($request,[
            // Check Values Validity and if empty
            "schedule_preference"=>v::intVal()
        ]);

        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Grab value
        $sched_pref = CustomRequestHandler::getParam($request,"schedule_preference");

        // SAVE VALUES INTO DB
        $result = $this->worker->get_save_worker_schedule_preference($userID, $sched_pref);
        if($result['success'] == false){
            return $this->customResponse->is500Response($response, $result['data']);
        }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response, $result['data'] == true ? "Successfully updated" : "something went wrong" );
    }


    // == Hurry mode: Re-review Later - Nov 28
    // =================================================================================================
    // This function gets the workers preffered cities from the DB
    // it is referenced by the /preferred-cities route
    // @param Request & Response, @returns formatted response object with status & message
    public function get_preferred_cities(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        $preferred_cities = $this->get_cities($userID);

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $preferred_cities);
    }

    private function get_cities($userID){
        // Fetch data from the database
        $result = $this->worker->getPreferredCities_fromDB($userID);
        if($result['success'] == false){
            return $this->customResponse->is500Response($response, $result['data']);
        }

        $preferred_cities = [];
        // Convert Data into array
        for($x = 0; $x < count($result['data']);$x++){
            array_push($preferred_cities, $result['data'][$x]["city_id"]);
        }
        return $preferred_cities;
    }

    
    // // == Hurry mode: Re-review Later - Nov 29
    // // ADDITIONAL NOTE!!!
    // // SUPER ROUGH PATCH AND WORKAROUNDS DUE TO DEADLINE RUSH
    // // THIS PART OF APP NEEDS REVIEW & DEBUGGING
    // // WORKS ONLY IF YOU PRAY FOR FORGIVENESS
    // // =================================================================================================
    // // This function loads if the worker has a schedule preference (Only general)
    // // it is referenced by the /general-schedule route
    // // @param Request & Response, @returns formatted response object with status & message
    // public function save_preferred_cities(Request $request,Response $response){
    //     // Something was wrong with CORS error and we don't have much time to fix it
    //     // Something was wrong with how the bearer token was sent
    //     // disabling for now
    //     // temporary solution is to send it as a parameter along with the body

    //                     // // Get the bearer token from the Auth header
    //                     // $bearer_token = JSON_encode($request->getHeader("Authorization"));

    //                     // // Catch the response, on success it is an ID, on fail it has status and message
    //                     // $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    //                     // // Error handling
    //                     // if(is_array( $userID) && array_key_exists("status", $userID)){
    //                     //     return $this->customResponse->is401Response($response, $userID);
    //                     // }

    //     // VALIDATE VALUES RECEIVED FROM client
    //     $this->validator->validate($request,[
    //         // Check Values Validity and if empty
    //         "preferred_cities"=>v::notempty(),
    //         "token"=>v::notempty(),
    //     ]);

    //     // Return Validation Errors
    //     if($this->validator->failed())
    //     {
    //         $responseMessage = $this->validator->errors;
    //         return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
    //     }

    //     // Get the bearer token from the parameters
    //     $raw = CustomRequestHandler::getParam($request,"token");

    //     // Decode token
    //     $result =  GenerateTokenController::AuthenticateUserType( $raw, 2);

    //     // ^^ No error handling/ catch implemented for decode above. Server doesn't return anything when token/user is incorrect
    //     if($result['status'] == false){
    //         return $this->generateServerResponse(401, $result['message']);
    //     }

    //     $userID =  $result["data"]["jti"];
    //     $userData = [];
    //     // return $this->customResponse->is200Response($response,    $userID);
    //     // return $this->customResponse->is401Response($response, $result );

    //     // Authenticate user
    //     // Get user information from DB and check if user is deleted
    //     $result = $this->worker->is_deleted($userID);
    //     $isDeleted = intval($result["data"]["is_deleted"]) != 0;
    //     if($result["success"] == false){
    //         return $this->generateServerResponse(500, $result["data"]);
    //     }
    //     if($isDeleted){
    //         return $this->generateServerResponse(401, "The user is not available since it was deleted from the database.");
    //     }

    //     // Grab value
    //     $preferred_cities_client = CustomRequestHandler::getParam($request,"preferred_cities");
    //     // then process the raw data into something that our functions can use -> array
    //     // $cities_arr = explode(",",   $preferred_cities_client);
    //     // For debug
    //     // return $this->customResponse->is500Response($response,$cities_arr);

    //     // unlike last request no need to explode since it is already send as an array for some reason
    //     // This might be the fact because we removed the headers and sent the token in the body instead of the header
    //     $cities_arr = $preferred_cities_client;

    //     // Validate cities array to ensure that the values are between 1-12 (Current entries in DB); TODO Dynamic this
    //     for($x = 0; $x < count($cities_arr); $x++){
    //         if($cities_arr[$x] < 1 || $cities_arr[$x] > 12){
    //             return $this->customResponse->is400Response($response,$this->generateServerResponse(400, "Key Values are only from 1-12. A key has been detected that is out of range."));
    //         }
    //     }

    //     // Grab current values in the DB
    //     $preferred_cities_db = $this->get_cities($userID);
    //     // For debug
    //     // return $this->customResponse->is500Response($response,$preferred_cities_db);

    //     // Compare client & Db, Sort values from add & delete (cities to add, cities to delete)
    //         // if not in db, add. 
    //     $cities_toAdd = [];
    //     for($x = 0; $x < count($cities_arr); $x++){
    //         if(!in_array($cities_arr[$x], $preferred_cities_db)){
    //             array_push($cities_toAdd , $cities_arr[$x]);
    //         }
    //     }
    //         // If not in client, delete.
    //     $cities_toDelete = [];
    //     for($x = 0; $x < count($preferred_cities_db); $x++){
    //         if(!in_array($preferred_cities_db[$x], $cities_arr)){
    //             array_push($cities_toDelete, $preferred_cities_db[$x]);
    //         }
    //     }
    //     // $debug=[];
    //     // $debug['add'] =  $cities_toAdd;
    //     // $debug['delete'] =   $cities_toDelete;
    //     // $debug['client'] =  $cities_arr;
    //     // $debug['db'] =   $preferred_cities_db;
    //     // return $this->customResponse->is200Response($response, $debug );

    //     $formattedResponse = [];
    //     $formattedResponse["message"] = "Nothing added or deleted. Selection of cities are the same from previous saved values.";
    //     if(count($cities_toAdd) !== 0 || count($cities_toDelete) !== 0){
    //         // SAVE VALUES INTO DB
    //         $result = $this->worker->savePreferredCities_intoDB($userID, $cities_toAdd, $cities_toDelete);
    //         if($result['success'] == false){
    //             return $this->customResponse->is500Response($response, $result['data']);
    //         }
    //         $formattedResponse["message"] = "Cities successfully added/removed.";
    //         $formattedResponse["resultBool"] = $result['data'];
    //         $formattedResponse["debug"] = $result['debug'];
    //         $formattedResponse["DBsuccess"] = $result['success'];
    //     }

    //     // Return information needed for personal info page
    //     return $this->customResponse->is200Response($response,  $formattedResponse );
    //     // return $this->customResponse->is200Response($response,  $userID );
    // }


    
// == Hurry mode: Re-review Later - Nov 29
    // ADDITIONAL NOTE!!!
    // SUPER ROUGH PATCH AND WORKAROUNDS DUE TO DEADLINE RUSH
    // THIS PART OF APP NEEDS REVIEW & DEBUGGING
    // WORKS ONLY IF YOU PRAY FOR FORGIVENESS
    // =================================================================================================
    // This function loads if the worker has a schedule preference (Only general)
    // it is referenced by the /general-schedule route
    // @param Request & Response, @returns formatted response object with status & message
    public function save_preferred_cities(Request $request,Response $response){
        // Something was wrong with CORS error and we don't have much time to fix it
        // Something was wrong with how the bearer token was sent
        // disabling for now
        // temporary solution is to send it as a parameter along with the body

                        // Get the bearer token from the Auth header
                        $bearer_token = JSON_encode($request->getHeader("Authorization"));

                        // Catch the response, on success it is an ID, on fail it has status and message
                        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

                        // Error handling
                        if(is_array( $userID) && array_key_exists("status", $userID)){
                            return $this->customResponse->is401Response($response, $userID);
                        }

        // VALIDATE VALUES RECEIVED FROM client
        $this->validator->validate($request,[
            // Check Values Validity and if empty
            "preferred_cities"=>v::notEmpty(),
        ]);

        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Grab value
        $preferred_cities_client = CustomRequestHandler::getParam($request,"preferred_cities");
        // then process the raw data into something that our functions can use -> array
        $cities_arr = explode(",",   $preferred_cities_client);
        // For debug
        // return $this->customResponse->is500Response($response,$cities_arr);

        // unlike last request no need to explode since it is already send as an array for some reason
        // This might be the fact because we removed the headers and sent the token in the body instead of the header
        // $cities_arr = $preferred_cities_client;

        // Validate cities array to ensure that the values are between 1-12 (Current entries in DB); TODO Dynamic this
        for($x = 0; $x < count($cities_arr); $x++){
            if($cities_arr[$x] < 1 || $cities_arr[$x] > 12){
                return $this->customResponse->is400Response($response,$this->generateServerResponse(400, "Key Values are only from 1-12. A key has been detected that is out of range."));
            }
        }

        // Grab current values in the DB
        $preferred_cities_db = $this->get_cities($userID);
        // For debug
        // return $this->customResponse->is500Response($response,$preferred_cities_db);

        // Compare client & Db, Sort values from add & delete (cities to add, cities to delete)
            // if not in db, add. 
        $cities_toAdd = [];
        for($x = 0; $x < count($cities_arr); $x++){
            if(!in_array($cities_arr[$x], $preferred_cities_db)){
                array_push($cities_toAdd , $cities_arr[$x]);
            }
        }
            // If not in client, delete.
        $cities_toDelete = [];
        for($x = 0; $x < count($preferred_cities_db); $x++){
            if(!in_array($preferred_cities_db[$x], $cities_arr)){
                array_push($cities_toDelete, $preferred_cities_db[$x]);
            }
        }
        // $debug=[];
        // $debug['add'] =  $cities_toAdd;
        // $debug['delete'] =   $cities_toDelete;
        // $debug['client'] =  $cities_arr;
        // $debug['db'] =   $preferred_cities_db;
        // return $this->customResponse->is200Response($response, $debug );

        $formattedResponse = [];
        $formattedResponse["message"] = "Nothing added or deleted. Selection of cities are the same from previous saved values.";
        if(count($cities_toAdd) !== 0 || count($cities_toDelete) !== 0){
            // SAVE VALUES INTO DB
            $result = $this->worker->savePreferredCities_intoDB($userID, $cities_toAdd, $cities_toDelete);
            if($result['success'] == false){
                return $this->customResponse->is500Response($response, $result['data']);
            }
            $formattedResponse["message"] = "Cities successfully added/removed.";
            $formattedResponse["resultBool"] = $result['data'];
            $formattedResponse["debug"] = $result['debug'];
            $formattedResponse["DBsuccess"] = $result['success'];
        }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $formattedResponse );
        // return $this->customResponse->is200Response($response,  $userID );
    }
    
    // =================================================================================================
    // This function loads all the information needed for the review page
    // it is referenced by the /general-schedule route
    // @param Request & Response, @returns formatted response object with status & message
    public function get_registration_review(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        
        $data = [];
        // Get user info needed for registration review
            // Worker Full Name string 
            // worker mobile number string
            $userData = $this->user->getUserByID($userID);
            if($userData["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $userData["data"]) );
            }
            // $userData["expertiseList"] = $expertiseList["data"];
            $data['name'] = ucfirst($userData["data"]["first_name"])." ".ucfirst($userData["data"]["last_name"]);
            $data['mobile'] = $userData["data"]["phone_no"];

            // Worker skills array of ids
            $skillList = $this->worker->getList_expertise($userID);
            if($skillList["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $skillList["data"]) );
            }
            // Convert the array to a formatted string
            if(count($skillList["data"]) == 0 || $skillList["data"] == null || empty($skillList["data"])){
                $data['skills'] = "--"; // we still want the page to load so no send error
            }
            $skillsString = "";
            for($x = 0; $x < count($skillList["data"]); $x++){
                $skillsString = $skillsString.ucfirst($skillList["data"][$x]["name"]);
                if(count($skillList["data"])-1 != $x ){
                    $skillsString = $skillsString.", ";
                }
            }
            $data['skills'] = $skillsString;

            // Get worker information from table
            $workerData = $this->worker->getWorkerRegistrationReviewInfo_ByID($userID);
            if( $workerData["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $workerData["data"]) );
            }
            // Construct Salary String
            $wrate = $workerData["data"]["default_rate"];
            $wtype = strtolower($workerData["data"]["type"]);
            $data['salary'] = "$wrate/per $wtype";
            // Add sched pref info
            $data['has_sched_pref'] = $workerData["data"]["has_schedule_preference"] == 0 ? "false" : "true";
            // Add booking lead info
            $data['booking_lead'] =  $workerData["data"]["lead_time"];
            // Add notice lead info
            $data['notice_lead'] =  $workerData["data"]["notice_time"];

            // Get NBI Information from table
            $nbiInfo = $this->worker->get_nbi_information($userID);
            if( $nbiInfo["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $nbiInfo["data"]) );
            }
            $data['nbiNo'] = $nbiInfo["data"]["clearance_no"];
            $data['expiration'] = $nbiInfo["data"]["expiration_date"];

            // Get list of cities from DB
            $citiesList= $this->worker->getPreferredCities_fromDB($userID, true);
            if(  $citiesList["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $citiesList["data"]) );
            }
            // Convert the array to a formatted string
            if(count($citiesList["data"]) == 0 || $citiesList["data"] == null || empty($citiesList["data"])){
                $data['cities'] = "--"; // we still want the page to load so no send error
            }
            $citiesString = "";
            for($x = 0; $x < count($citiesList["data"]); $x++){
                $citiesString =  $citiesString.ucfirst($citiesList["data"][$x]["city_name"]);
                if(count($citiesList["data"])-1 != $x ){
                    $citiesString =  $citiesString.", ";
                }
            }
            $data['cities'] =  $citiesString;

            // GET SCHED VALUES FROM DB
            $sched_result = $this->worker->get_save_worker_schedule_preference($userID);
            if($sched_result['success'] == false){
                return $this->customResponse->is500Response($response, $sched_result['data']);
            }

            $data_sched = $sched_result['data'];

            $formattedData = [];
            $formattedData["id"] = $data_sched["id"];
            $formattedData["has_schedule_preference"] = $data_sched["has_schedule_preference"];
   
            $scheduleData = [];
            $scheduleData["id"] = $data_sched["id"];
            $scheduleData["is_monday_off"] = $data_sched["is_monday_off"];
            $scheduleData["monday_start_time"] = $data_sched["monday_start_time"];
            $scheduleData["monday_end_time"] = $data_sched["monday_end_time"];
            $scheduleData["is_tuesday_off"] = $data_sched["is_tuesday_off"];
            $scheduleData["tuesday_start_time"] = $data_sched["tuesday_start_time"];
            $scheduleData["tuesday_end_time"] = $data_sched["tuesday_end_time"];
            $scheduleData["is_wednesday_off"] = $data_sched["is_wednesday_off"];
            $scheduleData["wednesday_start_time"] = $data_sched["wednesday_start_time"];
            $scheduleData["wednesday_end_time"] = $data_sched["wednesday_end_time"];
            $scheduleData["is_thursday_off"] = $data_sched["is_thursday_off"];
            $scheduleData["thursday_start_time"] = $data_sched["thursday_start_time"];
            $scheduleData["thursday_end_time"] = $data_sched["thursday_end_time"];
            $scheduleData["is_friday_off"] = $data_sched["is_friday_off"];
            $scheduleData["friday_start_time"] = $data_sched["friday_start_time"];
            $scheduleData["friday_end_time"] = $data_sched["friday_end_time"];
            $scheduleData["is_saturday_off"] = $data_sched["is_saturday_off"];
            $scheduleData["saturday_start_time"] = $data_sched["saturday_start_time"];
            $scheduleData["saturday_end_time"] = $data_sched["saturday_end_time"];
            $scheduleData["is_sunday_off"] = $data_sched["is_sunday_off"];
            $scheduleData["sunday_start_time"] = $data_sched["sunday_start_time"];
            $scheduleData["sunday_end_time"] = $data_sched["sunday_end_time"];
   
            $formattedData["schedule_data"] = $scheduleData;
   
            $lead_notice_time_data = [];
            $lead_notice_time_data["notice_time"] = $data_sched["notice_time"]; 
            $lead_notice_time_data["lead_time"] = $data_sched["lead_time"]; 
   
            $formattedData["lead_notice_time_data"] =  $lead_notice_time_data ;



            // Get list of Certifications
            // <TODO> since we don't have save fature for certifications yet.
            $certificationsList = [];
            $certificationsString = "";
            //return $this->customResponse->is200Response($response,  $citiesList);

            //$data = [];
            // $data['name'] = "Jose Santos";
            // $data['mobile'] = "099-222-2222";
            // $data['skills'] = "Electrical, Carpentry";
            // $data['salary'] = "300.00 /per day";
            $data['cert'] = count($certificationsList) == 0 ? "none" : $certificationsString;
            // $data['nbiNo'] = "2009182378";
            // $data['expiration'] = "09/12/2022";
            // $data['has_sched_pref'] = "false";
            // $data['booking_lead'] = "1 month/s";
            // $data['notice_lead'] = "3 day/s";
            // $data['cities'] = "Cebu City, mandaue, Talisay";
             $data['schedule_data'] =  $formattedData['schedule_data'];
             $data['lead_notice_time_data'] =  $formattedData['lead_notice_time_data'];

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $data );
    }


    // Nove - 30 - In a Hurry Review Later
    // =================================================================================================
    // This function changes the user's registration status to complete and submits a support ticket for evaluation
    // it is referenced by submit-application
    // @param Request & Response, @returns formatted response object with status & message
    public function submit_application(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        // Complete the worker registration
            // Submit the application and create a support ticket
            $result = $this->worker->completeWorkerRegistration($userID);
            if(  $result["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $result["data"]) );
            }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  "Sucessful submission of application.");
    }


    // =================================================================================
    // =================================================================================
    // =================================================================================
    //  Dec 10

    public function save_specific_schedule(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }


        // VALIDATE VALUES RECEIVED FROM USER
        $this->validator->validate($request,[
            // Check Values Validity and if empty
            "end-time-Fri"=>v::notEmpty(),
            "end-time-Mon"=>v::notEmpty(),
            "end-time-Sat"=>v::notEmpty(),
            "end-time-Sun"=>v::notEmpty(),
            "end-time-Thu"=>v::notEmpty(),
            "end-time-Tue"=>v::notEmpty(),
            "end-time-Wed"=>v::notEmpty(),
            "start-time-Fri"=>v::notEmpty(),
            "start-time-Mon"=>v::notEmpty(),
            "start-time-Sat"=>v::notEmpty(),
            "start-time-Sun"=>v::notEmpty(),
            "start-time-Thu"=>v::notEmpty(),
            "start-time-Tue"=>v::notEmpty(),
            "start-time-Wed"=>v::notEmpty(),
            "lead_time"=>v::notEmpty(),
            "notice_time"=>v::notEmpty(),
        ]);

        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        $this->validator->validate($request,[
            // Check Values Validity and if empty
            "end-time-Fri"=>v::time(),
            "end-time-Mon"=>v::time(),
            "end-time-Sat"=>v::time(),
            "end-time-Sun"=>v::time(),
            "end-time-Thu"=>v::time(),
            "end-time-Tue"=>v::time(),
            "end-time-Wed"=>v::time(),
            "start-time-Fri"=>v::time(),
            "start-time-Mon"=>v::time(),
            "start-time-Sat"=>v::time(),
            "start-time-Sun"=>v::time(),
            "start-time-Thu"=>v::time(),
            "start-time-Tue"=>v::time(),
            "start-time-Wed"=>v::time(),
        ]);

        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Grab value & set values
        $sched_pref = 1;
        $e_Fri = CustomRequestHandler::getParam($request,"end-time-Fri");
        $e_Mon = CustomRequestHandler::getParam($request,"end-time-Mon");
        $e_Sat = CustomRequestHandler::getParam($request,"end-time-Sat");
        $e_Sun = CustomRequestHandler::getParam($request,"end-time-Sun");
        $e_Thu = CustomRequestHandler::getParam($request,"end-time-Thu");
        $e_Tue = CustomRequestHandler::getParam($request,"end-time-Tue");
        $e_Wed = CustomRequestHandler::getParam($request,"end-time-Wed");
        $s_Fri = CustomRequestHandler::getParam($request,"start-time-Fri");
        $s_Mon = CustomRequestHandler::getParam($request,"start-time-Mon");
        $s_Sat = CustomRequestHandler::getParam($request,"start-time-Sat");
        $s_Sun = CustomRequestHandler::getParam($request,"start-time-Sun");
        $s_Thu = CustomRequestHandler::getParam($request,"start-time-Thu");
        $s_Tue = CustomRequestHandler::getParam($request,"start-time-Tue");
        $s_Wed = CustomRequestHandler::getParam($request,"start-time-Wed");
        $d_Fri = CustomRequestHandler::getParam($request,"dayoff-input-Fri");
        $d_Mon = CustomRequestHandler::getParam($request,"dayoff-input-Mon");
        $d_Tue = CustomRequestHandler::getParam($request,"dayoff-input-Tue");
        $d_Wed = CustomRequestHandler::getParam($request,"dayoff-input-Wed");
        $d_Thu = CustomRequestHandler::getParam($request,"dayoff-input-Thu");
        $d_Sat = CustomRequestHandler::getParam($request,"dayoff-input-Sat");
        $d_Sun = CustomRequestHandler::getParam($request,"dayoff-input-Sun");
        $lead_time = CustomRequestHandler::getParam($request,"lead_time");
        $notice_time = CustomRequestHandler::getParam($request,"notice_time");

        $result = "";
        // SAVE VALUES INTO DB
        $result = $this->worker->specific_worker_schedule_preference($userID, $sched_pref,
            $e_Fri, $e_Mon, $e_Sat, $e_Sun, $e_Thu, $e_Tue, $e_Wed,
            $s_Fri, $s_Mon, $s_Sat, $s_Sun, $s_Thu, $s_Tue, $s_Wed,
            $d_Fri, $d_Mon, $d_Sat, $d_Sun, $d_Thu, $d_Tue, $d_Wed,
            $lead_time, $notice_time
        );
        if($result['success'] == false){
            return $this->customResponse->is500Response($response, $result['data']);
        }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $result );

        // For Debugging purposes
        // return $this->customResponse->is200Response($response,  $userID );
        // return $this->customResponse->is200Response($response,  "This route works");
    }






    // == Hurry mode: Re-review Later
    // =================================================================================================
    // This function loads if the worker has a schedule preference (Only general)
    // it is referenced by the /general-schedule route
    // @param Request & Response, @returns formatted response object with status & message
    public function templateQuickGrab(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $userID );
    }
    




    
}