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
    $response['status'] = $status;
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
        // userID in JWT
        $home_id = CustomRequestHandler::getParam($request,"home_id");
        $job_size_id = CustomRequestHandler::getParam($request,"job_size_id");
        $required_expertise_id = CustomRequestHandler::getParam($request,"required_expertise_id");
        $job_description = CustomRequestHandler::getParam($request,"job_description");
        $rate_offer = CustomRequestHandler::getParam($request,"rate_offer");
        $isExactSchedule = CustomRequestHandler::getParam($request,"is_exact_schedule");
        $rate_type_id = CustomRequestHandler::getParam($request,"rate_type_id");
        $preferred_date_time = CustomRequestHandler::getParam($request,"preferred_date_time");
        $project_name = CustomRequestHandler::getParam($request,"project_name");


        // adds project
        $result = $this->file->saveProject(
            $userID,
            $home_id, 
            $job_size_id, 
            $required_expertise_id,
            $job_description, 
            $rate_offer,   
            $isExactSchedule,
            $rate_type_id, 
            $preferred_date_time, 
            $project_name
        );
        // Error handling
        if(  $result['success'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $result['data']) );
        }

        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,    $result );
        //return $this->customResponse->is200Response($response,  "This route works");
    }



    








    public function getProjects(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        // Get ongoing projects
        $ongoingProj =  $this->file->getOngoingProjects($userID);
        // Error handling
        if(   $ongoingProj['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $ongoingProj['data']) );
        }

        // Get ongoing job orders
        $ongoingJobOrders =  $this->file->getOngoingJobOrders($userID);
        // Error handling
        if(   $ongoingJobOrders['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $ongoingJobOrders['data']) );
        }

        // Get closed projects
        $closedProj =  $this->file->getClosedProjects($userID);
        // Error handling
        if(   $ongoingProj['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $closedProj['data']) );
        }

        // send data back
        $data = [];
        $data['ongoingJobPosts'] = $ongoingProj['data'];
        $data['ongoingProjects'] = $ongoingJobOrders['data'];
        $data['closedProjects'] = $closedProj['data'];

        
        // Return information needed for personal info page
        return $this->customResponse->is200Response($response, $data);
    }




    public function getSingleProject(Request $request,Response $response, array $args){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Catch the response, on success it is an ID, on fail it has status and message
        $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

        // Error handling
        if(is_array( $userID) && array_key_exists("status", $userID)){
            return $this->customResponse->is401Response($response, $userID);
        }

        // Get the necessary data needed to pass into the variables
        $jobPostID = $args['id'];

        // Validate Data & Return Validation Errors
        if(!v::intVal()->validate($jobPostID))
        {
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, "wrong api route argument"));
        }


        // Get Single job post
        $singleJobPost=  $this->file->getSingleJobPost($jobPostID);
        // Error handling & Verification
        if(   $singleJobPost['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $singleJobPost['data']) );
        }
        if($singleJobPost['data'] == false){
            return $this->customResponse->is500Response($response,$this->generateServerResponse(404, "404: Job Post cannot be found"));
        }
        if($singleJobPost['data']['homeowner_id'] != $userID){
            return $this->customResponse->is401Response($response,$this->generateServerResponse(401, "401: Unauthorized access to job post."));
        }


        // Get Single Job Order Associated with post
        $singleJobOrder =  $this->file->getSingleJobOrder($jobPostID);
        // Error handling 
        if( $singleJobOrder['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $singleJobOrder['data']) );
        }
        $jo =  $singleJobOrder['data'];

        $singleBill = null;
        $singleRating = null;
        if(  $jo !== false ){
            // Get Single Job Bill Associated with post
            $singleBill =  $this->file->getSingleBill($jo['id']);
            // Error handling 
            if( $singleBill['success'] !== true){
                return $this->$singleBill->is500Response($response, $this->generateServerResponse(500, $singleBill['data']) );
            }

            // Get single review associated with post
            $singleRating =  $this->file->getSingleRating($jo['id']);
            // Error handling 
            if(  $singleRating['success'] !== true){
                return $this->$singleBill->is500Response($response, $this->generateServerResponse(500,  $singleRating['data']) );
            }
        }

        // send data back
        $data = [];
        $data['singleJobPost'] = $singleJobPost['data'];
        $data['singleJobOrder'] = $jo;
        $data['singleBill'] = $singleBill == null ? false : $singleBill['data'];
        $data['singleReview'] = $singleRating == null ? false : $singleRating['data'] ;

        
        // Return information needed for personal info page
        return $this->customResponse->is200Response($response, $data);
    }







// ===============================================
// Dec 7


public function getAllAddresses(Request $request,Response $response){

    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,  $userID));
    }

    // GET USER DEFAULT ADDRESS
    $defaultHomeID = $this->file->getUserDefaultAddress($userID);
    // Error handling
    if($defaultHomeID['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $defaultHomeID['data']) );
    }

    // GET USER ALL ADDRESS
    $allAddress = $this->file->getUsersSavedAddresses($userID);
    // Error handling
    if(  $allAddress['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $allAddress['data']) );
    }

    $formData = [];

    $formData["defaultHome_id"] = $defaultHomeID['data'][0]['default_home_id'];
    $formData["allAddress"] =   $allAddress['data'];

    // Return information needed for personal info page
   // return $this->customResponse->is200Response($response,  $userID );
     return $this->customResponse->is200Response($response, $formData);

}



public function updateJobPost(Request $request,Response $response, array $args){

    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // VALIDATION THE INFORMATION (CHECK IF EMPTY)
     $this->validator->validate($request,[
        "date"=>v::notEmpty(),
        "home_id"=>v::notEmpty(),
        "job_size_id"=>v::notEmpty(),
        "rate_offer"=>v::notEmpty(),
        "rate_type_id"=>v::notEmpty(),
        "time"=>v::notEmpty(),
     ]);

    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    // VALIDATION THE INFORMATION (SECOND VALIDATION FILTER CHECK VALID VALUES)
    $this->validator->validate($request,[
        "date"=>v::date(),
        "home_id"=>v::intVal(),
        "job_size_id"=>v::intVal(),
        "job_size_id"=>v::between(1, 3),
        "rate_offer"=>v::number(),
        "rate_type_id"=>v::intVal(),
        "rate_type_id"=>v::between(1, 4),
        "time"=>v::time(),
    ]);

    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }


    // GET NECESSARY INFORMATION FOR UPDATING THE POST
    $date = CustomRequestHandler::getParam($request,"date");
    $time = CustomRequestHandler::getParam($request,"time");
    // ----
    $post_id = $args['id']; 
    $home_id = CustomRequestHandler::getParam($request,"home_id");
    $job_size_id = CustomRequestHandler::getParam($request,"job_size_id");
    $job_description = CustomRequestHandler::getParam($request,"job_description");
    $rate_offer = CustomRequestHandler::getParam($request,"rate_offer");
    $rate_type_id = CustomRequestHandler::getParam($request,"rate_type_id");
    $preferred_date_time = $date.' '.$time;
    $job_post_name = CustomRequestHandler::getParam($request,"job_post_name");


    $isValidHome = false;
    // CHECK TO SEE IF THE HOME ID IS AMONG THE LIST OF USER'S ADDRESSES
    // GET USER ALL ADDRESS
    $allAddress = $this->file->getUsersSavedAddresses($userID);
    // Error handling
    if(  $allAddress['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $allAddress['data']) );
    }
    $add_arr = $allAddress['data'];
    if(count($add_arr) == 0){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404,  "This user does not have any saved addresses. Access denied to other homeowners address."));
    } else {
        for($x = 0; $x < count($add_arr); $x++){
            if($add_arr[$x]['home_id'] == $home_id){
                $isValidHome = true;
                break;
            }
        }
    }
    if($isValidHome == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404,  "The address cannot be found in the user's addressbook. Access denied to other homeowners address."));
    }


    // UPDATE POST - Everything good
    $result = $this->file->updateProject(
        $post_id,
        $home_id,
        $job_size_id,
        $job_description,
        $rate_offer,
        $rate_type_id,
        $preferred_date_time,
        $job_post_name
    );

    // Error handling
    if( $result['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(500,  $result['data']) );
    }


    $formData = [];
    $formData['result'] =  $result['data'];

    // // // For debugging purposes
    // // $formData['id'] = $post_id;
    // // $formData['home_id'] = $home_id;
    // // $formData['job_size_id'] = $job_size_id;
    // // $formData['job_description'] = $job_description;
    // // $formData['rate_offer'] =  $rate_offer;
    // // $formData['rate_type_id'] =   $rate_type_id;
    // // $formData['preferred_date_time'] = $preferred_date_time;
    // // $formData['job_post_name'] = $job_post_name;

    // Return information needed for personal info page
     return $this->customResponse->is200Response($response, $formData);

    // // // For debugging purposes
    // return $this->customResponse->is200Response($response,  $userID );
    //return $this->customResponse->is200Response($response, "This route works".$args['id']);
    // return $this->customResponse->is200Response($response,  $isValidHome);
}




public function cancelJobPost(Request $request,Response $response, array $args){

    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }


    // GET NECESSARY INFORMATION FOR CANCELLING THE POST
    $post_id = $args['id']; 
    $reason = CustomRequestHandler::getParam($request,"cancellation_reason");

    // GET THE USER'S POST DATA
    $postData = $this->file->getJobPostUserID($post_id);
    // Error handling
    if($postData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $postData['data']) );
    }
    if( $postData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Post not found") );
    }
    if($postData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this post.") );
    }

    // CANCEL THE POST
    $result = $this->file->cancelJobPost($post_id, $reason);
    // Error handling
    if(  $result['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $result['data']) );
    }

    $formData = [];
    $formData['result'] =  $result;

    // Return information needed for personal info page
     return $this->customResponse->is200Response($response, $formData);

    // For debugging purposes
    // return $this->customResponse->is200Response($response,  $postData );
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response, "This route works");
}




// =====================================================
// Dec 8

public function cancelJobOrder(Request $request,Response $response, array $args){

    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        // return $this->customResponse->is401Response($response, $userID);
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $userID) );
    }


    // GET NECESSARY INFORMATION FOR CANCELLING THE POST
    $order_id = $args['id']; 
    $reason = CustomRequestHandler::getParam($request,"cancellation_reason");

    // GET THE USER'S ORDER DATA
    $orderData = $this->file->getJobOrderUserID($order_id);
    // Error handling
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }
    if( $orderData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Order not found") );
    }
    if( $orderData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this order.") );
    }

    // CANCEL THE ORDER
    $result = $this->file->cancelJobOrder($order_id , $reason, $userID);
    // Error handling
    if(  $result['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $result['data']) );
    }

    $formData = [];
    $formData['result'] =  $result;

    // // Return information needed for personal info page
     return $this->customResponse->is200Response($response, $formData);

    // For debugging purposes
    // return $this->customResponse->is200Response($response,   $orderData );
    // return $this->customResponse->is200Response($response,  $userID."post_id". $post_id."reason".$reason  );
    // return $this->customResponse->is200Response($response, "This route works");
}


// This function is for when the worker does not start the job order and never starts
// So the User can cancel and repost the job post
public function cancelRepostOrder(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // Check if empty
    $this->validator->validate($request,[
        // Check if empty
        "date"=>v::notEmpty(),
        "time"=>v::notEmpty()
    ]);

    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    // Check if date time
    $this->validator->validate($request,[
        // Check if empty
        "date"=>v::date(),
        "time"=>v::time()
    ]);

    // Error Handling
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }


    // GET NECESSARY INFORMATION FOR CANCELLING THE POST & Validation
    $order_id = $args['id']; 
    $reason = CustomRequestHandler::getParam($request,"cancellation_reason");
    $date = CustomRequestHandler::getParam($request,"date");
    $time = CustomRequestHandler::getParam($request,"time");
    $preferred_date_time = $date.' '.$time;

    // GET THE USER'S ORDER DATA
    $orderData = $this->file->getJobOrderUserID($order_id);
    // Error handling
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }
    if( $orderData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Order not found") );
    }
    if( $orderData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this order.") );
    }

    // // GET THE USER'S POST DATA
    $post_id = $orderData['data']['job_post_id'];
    $postData = $this->file->getSingleJobPost( $post_id);
    // Error handling
    if( $postData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $postData['data']) );
    }
    if( $postData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Post not found") );
    }

    // Extract Values from post data
    $home_id = $postData['data']['home_id'];
    $job_size_id = $postData['data']['job_size_id'];
    $required_expertise_id = $postData['data']['required_expertise_id'];
    $job_description = $postData['data']['job_description'];
    $rate_offer = $postData['data']['rate_offer'];  
    $isExactSchedule = $postData['data']['is_exact_schedule'];
    $rate_type_id = $postData['data']['rate_type_id']; 
    $project_name = $postData['data']['job_post_name'];

    $systemGenerated = "Homehero did not show up for the scheduled job order.";
    if($reason != "" && $reason != null){
        $systemGenerated = $systemGenerated." Additional Details: ".$reason;
    }

    $result = "";
    // CANCEL & REPOST
    $result = $this->file->cancelOrder_DuplicatePost(
        $order_id, //
        $systemGenerated, //
        $userID, //
        $home_id, 
        $job_size_id, 
        $required_expertise_id,
        $job_description, 
        $rate_offer,   
        $isExactSchedule,
        $rate_type_id, 
        $preferred_date_time, 
        $project_name
    );

    // Error handling
    if(  isset($result['success']) && $result['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $result['data']) );
    }


    // Return information needed for personal info page
    return $this->customResponse->is200Response($response, $result);

    // // For Debugging Purposes
    // return $this->customResponse->is200Response($response,  $postData );
    // return $this->customResponse->is200Response($response,  $post_id );
    // return $this->customResponse->is200Response($response,  $orderData );
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}





// Checks DB if a support ticket already has been created
// returns false if none and returns support ticket info if there is
public function hasJobIssue(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // GET NECESSARY INFORMATION FOR CANCELLING THE POST & Validation
    $order_id = $args['id']; 
    $reason = CustomRequestHandler::getParam($request,"cancellation_reason");

    // GET THE USER'S ORDER DATA
    $orderData = $this->file->getJobOrderUserID($order_id);
    // Error handling
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }
    if( $orderData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Order not found") );
    }
    if( $orderData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this order.") );
    }

    // CHECK IF THE DB ALREADY HAS A SUPPORT TICKET CREATED LINKED TO THE JOB ORDER
    $supportTicket = $this->file->checkJobOrderIssues($order_id);
    // Error handling
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }

    // GET THE LAST ACTION OF THE SUPPORT TICKET
    $lastAction = null;
    if($supportTicket['data'] != false){
        $lastAction = $this->file->getSupportTicketLastAction($supportTicket['data']['support_ticket_id']);
        // Error handling
        if( $lastAction['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $lastAction['data']) );
        }
    }

    $formData = [];
    $formData['support_ticket_info'] =  $supportTicket['data'];
    $formData['lastSupportTicketAction'] =  $lastAction == null ? null : $lastAction['data'];

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $formData );

    // For debugging
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}







public function reportJobIssue(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // GET NECESSARY INFORMATION FOR CREATING SUPPORT TICKET & Validation
    $order_id = $args['id']; 
    $type = $args['type']; 
    $reason = CustomRequestHandler::getParam($request,"author_description");

    // GET THE USER'S ORDER DATA
    $orderData = $this->file->getJobOrderUserID($order_id);
    // Error handling, if order belongs to user
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }
    if( $orderData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Order not found") );
    }
    if( $orderData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this order.") );
    }

    // CHECK IF THE DB ALREADY HAS A SUPPORT TICKET CREATED LINKED TO THE JOB ORDER
    $supportTicket = $this->file->checkJobOrderIssues($order_id);
    // Error handling
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }

    // IF THERE IS NO SUPPORT TICKET CREATE ONE, OTHERWISE GET LAST ACTION
    $lastAction = null;
    $newSupportTicketCreated = false;
    if($supportTicket['data'] != false){
        // GET LAST ACTION
        $lastAction = $this->file->getSupportTicketLastAction($supportTicket['data']['support_ticket_id']);
        // Error handling
        if( $lastAction['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $lastAction['data']) );
        }
    } else {
        $message = $type == 1 ? "Worker did not show up for scheduled job post." : "";
        $systemMessage = $type == 1 ? " SUBMITTED A JOB ISSUE - WORKER NO SHOW" : " SUBMITTED A JOB ORDER ISSUE";
        $issueID = 7;

        if($reason != null && $reason != ""){
            $message = $message." Additional information: ".$reason;
        }

        // CREATE A SUPPORT TICKET
        $newSupportTicketCreated = $this->file->createJobIssueTicket(
            $userID,    // author
            $issueID,          // subcategory
            $message,   // authorDesc
            "HOMEOWNER#".$userID.$systemMessage, // systemDesc
            0,          // has images
            $order_id   // orderID
        );
        // Error handling
        if( $newSupportTicketCreated['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $newSupportTicketCreated['data']) );
        }
    }

    $formData = [];
    $formData['support_ticket_info'] =  $supportTicket['data'];
    $formData['lastSupportTicketAction'] =  $lastAction == null ? null : $lastAction['data'];
    $formData['newSupportTicketCreated'] =  $newSupportTicketCreated;



    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $formData);

    // // For debugging
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
    // return $this->customResponse->is200Response($response,  $args['type']);
}




public function updateSchedule(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // Validation for schedule date & time
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "date"=>v::notEmpty(),
            "time"=>v::notEmpty()
        ]);
    
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
    
        // Check if date time
        $this->validator->validate($request,[
            // Check if empty
            "date"=>v::date(),
            "time"=>v::time()
        ]);
    
        // Error Handling
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // GET NECESSARY INFORMATION FOR CREATING SUPPORT TICKET & Validation
    $post_id = $args['id']; 
    $date = CustomRequestHandler::getParam($request,"date");
    $time = CustomRequestHandler::getParam($request,"time");
    $preferred_date_time = $date.' '.$time;

    // Get the post and check if it is the userID (Validate)
    // GET THE USER'S POST DATA
    $postData = $this->file->getSingleJobPost($post_id);
    // Error handling
    if( $postData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $postData['data']) );
    }
    if( $postData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Post not found") );
    }
    if( $postData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this order.") );
    }

    // Update the job POST schedule information
    $result = $this->file->updatePostSchedule($post_id, $preferred_date_time);
    // Error handling
    if( $result['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $result['data']) );
    }

    $formData = [];
    $formData['result'] = "";


    // // Return information needed for personal info page
    return $this->customResponse->is200Response($response, $result);

    // For debugging purposes
    // return $this->customResponse->is200Response($response,  $postData );
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}



public function confirmPayment(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // GET NECESSARY INFORMATION FOR CREATING SUPPORT TICKET & Validation
    $order_id = $args['orderid']; 

    // GET THE USER'S ORDER DATA
    $orderData = $this->file->getJobOrderUserID($order_id);
    // Error handling, if order belongs to user
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }
    if( $orderData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Order not found") );
    }
    if( $orderData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this order.") );
    }

    // Update the bill
    $result = $this->file->completeCashPayment($order_id);
    // Error handling
    if( $result['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $result['data']) );
    }

    $formData = [];
    $formData['result'] = "";

    return $this->customResponse->is200Response($response,  $result);

    // For debugging purposes
    // return $this->customResponse->is200Response($response,  $orderData);
    // return $this->customResponse->is200Response($response,  $userID);
    // return $this->customResponse->is200Response($response,  "This route works");
}


public function saveRating(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // GET NECESSARY INFORMATION FOR Validation
    $order_id = $args['orderid']; 

    // GET THE USER'S ORDER DATA
    $orderData = $this->file->getJobOrderUserID($order_id);
    // Error handling, if order belongs to user
    if( $orderData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $orderData['data']) );
    }
    if( $orderData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Order not found") );
    }
    if( $orderData['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "This user does not have access to this order.") );
    }

    // CHECK IF A RATING ALREADY EXISTS
    $hasRating = $this->file->hasRating($order_id);
    // Error handling, if order belongs to user
    if(  $hasRating['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $hasRating['data']) );
    }

    // GET USER'S INPUT
        // Validation for user's input
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "quality"=>v::notEmpty(),
            "professionalism"=>v::notEmpty(),
            "reliability"=>v::notEmpty(),
            "punctuality"=>v::notEmpty()
        ]);
    
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
    
        // Check if date time
        $this->validator->validate($request,[
            // Check if value within range
            "quality"=>v::intVal()->between(1, 5),
            "professionalism"=>v::intVal()->between(1, 5),
            "reliability"=>v::intVal()->between(1, 5),
            "punctuality"=>v::intVal()->between(1, 5)
        ]);
    
        // Error Handling
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // GET NECESSARY INFORMATION FOR CREATING SUPPORT TICKET & Validation
    $quality = CustomRequestHandler::getParam($request,"quality");
    $professionalism = CustomRequestHandler::getParam($request,"professionalism");
    $reliability = CustomRequestHandler::getParam($request,"reliability");
    $punctuality = CustomRequestHandler::getParam($request,"punctuality");
    $comment = CustomRequestHandler::getParam($request,"comment");


    $new_rating_created = false;
    // ONLY CREATE A RATING IF ONE DOES NOT EXIST
    if(  $hasRating['data'] == false){
        $new_rating_created = true;
        // CREATE A RATING
        $result = $this->file->saveRating(
                                $order_id, 
                                $userID, 
                                $orderData['data']['worker_id'], 
                                $quality,
                                $professionalism,
                                $reliability,
                                $punctuality,
                                $comment
                            );
        // Error handling, if order belongs to user
        if( $result['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $result['data']) );
        }
    }

    $formData = [];
    $formData['has_rating'] =  $hasRating['data'];
    $formData['new_rating_created'] =  $new_rating_created;

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response, $formData);

    // For Debugging purposes
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}




















// public function templateQuickGrab(Request $request,Response $response){
//     // Get the bearer token from the Auth header
//     $bearer_token = JSON_encode($request->getHeader("Authorization"));

//     // Catch the response, on success it is an ID, on fail it has status and message
//     $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

//     // Error handling
//     if(is_array( $userID) && array_key_exists("status", $userID)){
//         return $this->customResponse->is401Response($response, $userID);
//     }

//     // Return information needed for personal info page
//     return $this->customResponse->is200Response($response,  $userID );
//     return $this->customResponse->is200Response($response,  "This route works");
// }








}