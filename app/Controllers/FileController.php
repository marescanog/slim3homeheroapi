<?php


namespace App\Controllers;

use App\Models\File;
use App\Models\User;
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

    protected $user;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->file = new File();

        $this->user = new User();

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
    if( $supportTicket['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $supportTicket['data']) );
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
            "HOMEOWNER #".$userID.$systemMessage, // systemDesc
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


// =============================================================================
// DEC 9

public function hasBillingIssue(Request $request,Response $response, array $args){
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

    // CHECK DB IF BILL WAS ALREADY GENERATED
    $billData = $this->file->getSingleBill($order_id);
    // Error handling
    if( $billData['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $billData['data']) );
    }
    if( $billData['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Bill not found") );
    }

    // CHECK IF THE DB ALREADY HAS A SUPPORT TICKET CREATED LINKED TO THE BILL
    $supportTicket = $this->file->checkBillingIssues($billData['data']['id']);
    // Error handling
    if( $supportTicket['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $supportTicket['data']) );
    }

    $formData = [];
    $formData['bill_data'] =  $billData['data'];
    $formData['support_ticket_info'] =  $supportTicket['data'];

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $formData);

    // // For debugging
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
    // return $this->customResponse->is200Response($response,  $args['type']);
}




public function createBillingIssue(Request $request,Response $response, array $args){
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
    
        // CHECK DB IF BILL WAS ALREADY GENERATED
        $billData = $this->file->getSingleBill($order_id);
        // Error handling
        if( $billData['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $billData['data']) );
        }
        if( $billData['data'] == false){
            return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "Bill not found") );
        }
    
        // CHECK IF THE DB ALREADY HAS A SUPPORT TICKET CREATED LINKED TO THE BILL
        $supportTicket = $this->file->checkBillingIssues($billData['data']['id']);
        // Error handling
        if( $supportTicket['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $supportTicket['data']) );
        }
    
        // // IF THERE IS NO SUPPORT TICKET CREATE ONE, OTHERWISE GET LAST ACTION
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
            $message = $reason;
            $systemMessage = " SUBMITTED A BILLING ISSUE";
            $issueID = 4;
    
            // CREATE A SUPPORT TICKET
            $newSupportTicketCreated = $this->file->createBillingIssueTicket(
                $userID,    // author
                $issueID,          // subcategory
                $message,   // authorDesc
                "HOMEOWNER#".$userID.$systemMessage, // systemDesc
                0,          // has images
                $billData['data']['id']   // billID
            );
            // Error handling
            if( $newSupportTicketCreated['success'] !== true){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $newSupportTicketCreated['data']) );
            }
        }
    
        $formData = [];
        $formData['bill_data'] =  $billData['data'];
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








// =============================================================
// Dec 11

public function getAccountSummary(Request $request,Response $response){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // Grab Profile Picture
    $profPicResult = $this->file->getProfilePic($userID);
    if(   $profPicResult['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,    $profPicResult['data']) );
    }

    // Grab User Information
    $accInfoResult = $this->file->getAccInfo($userID);
    if(  $accInfoResult['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $accInfoResult['data']) );
    }

    // Get Total Job Posts Made
    $totPostResult = $this->file->getTotalJobPosts($userID);
    if(  $totPostResult['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $totPostResult['data']) );
    }

    // Get Total Completed Projects
    $completedProjResult = $this->file->getTotalCompletedProjects($userID);
    if(  $completedProjResult['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $completedProjResult['data']) );
    }

    // Get Most Posted category
    $mostPostedCatResult = $this->file->getMostPostedCategory($userID);
    if(  $mostPostedCatResult['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $mostPostedCatResult['data']) );
    } 

    // Get Total Cancelleted projects
    $cancelledProjResult = $this->file->getTotalCancelledProjects($userID);
    if(  $cancelledProjResult['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $cancelledProjResult['data']) );
    } 

    // GET USER ALL ADDRESS
    $allAddress = $this->file->getUsersSavedAddresses($userID);
    // Error handling
    if(  $allAddress['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $allAddress['data']) );
    }

    $formData = [];
    $formData['profilePic'] = $profPicResult['data'];
    $formData['accInfo'] = $accInfoResult['data'];
    $formData['total_job_posts'] = $totPostResult['data']['total_job_posts'];
    $formData['total_completed_projects'] = $completedProjResult['data']['total_completed_projects'];
    $formData['most_posted_category'] = $mostPostedCatResult['data'] == false ? "You don't have any posted projects yet" : $mostPostedCatResult['data']['expertise'];
    $formData['total_cancelled_projects'] =  $cancelledProjResult['data']['total_cancelled_projects'];
    $formData['all_addresses'] =  $allAddress['data'];

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,   $formData );

    // For Debugging purposes
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}


// =========================================================================
// DEC 11 - NIGHT TIME


public function getFormForEditAddress(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // GET NECESSARY INFORMATION FOR Validation
    $home_id = $args['homeid']; 

    // GET USER SINGLE ADDRESS
    $singleAddress = $this->file->getSingleAddress($home_id);
    // Error handling
    if(  $singleAddress['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $singleAddress['data']) );
    }

    // VERIFY IF IT IS FOUND
    if(  $singleAddress['data'] ==  false ){
        return $this->customResponse->is400Response($response, $this->generateServerResponse(400,   "The address cannot be found") );
    }

    // VERIFY IF IT IS USER'S ADDRESS
    if(  $singleAddress['data']['homeowner_id'] !==  $userID ){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   "The user does not have access to this address") );
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

    // $formData["defaultHome"] = $defaultHomeID['data'];
    $formData["home_addr"] = $singleAddress['data'];
    $formData["cities"] = $cities['data'];
    $formData["barangays"] = $barangay['data'] ;
    $formData["hometype"] = $hometype['data'];



    // Return information needed for personal info page
    return $this->customResponse->is200Response($response, $formData );

    // For debugging purposes
    //     return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}





public function updateAddress(Request $request,Response $response, array $args){
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
    // Add Home ID Args
    $home_id = $args['homeid'];

    // Verify if the home belongs to the user
    // GET USER SINGLE ADDRESS
    $singleAddress = $this->file->getSingleAddress($home_id);
    // Error handling
    if(  $singleAddress['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $singleAddress['data']) );
    }

    // VERIFY IF IT IS FOUND
    if(  $singleAddress['data'] ==  false ){
        return $this->customResponse->is400Response($response, $this->generateServerResponse(400,   "The address cannot be found") );
    }

    // VERIFY IF IT IS USER'S ADDRESS
    if(  $singleAddress['data']['homeowner_id'] !==  $userID ){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   "The user does not have access to this address") );
    }

    // Update address
    $result = $this->file->updateAddress($userID, $street_no, $street_name,  $barangay_id,$home_type, $extra_address_info, $home_id );
    // Error handling
    if(  $result['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $result['data']) );
    }


    // // Return information needed for add project
    return $this->customResponse->is200Response($response,  $result);

    // FOR DEBUGGING PURPOSES
    //    return $this->customResponse->is200Response($response,  $userID);
    // return $this->customResponse->is200Response($response,  "This route works");
}





public function deleteAddress(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }


    // Get all necessary parameters
    // Add Home ID Args
    $home_id = $args['homeid'];

    // Verify if the home belongs to the user
    // GET USER SINGLE ADDRESS
    $singleAddress = $this->file->getSingleAddress($home_id);
    // Error handling
    if(  $singleAddress['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $singleAddress['data']) );
    }

    // VERIFY IF IT IS FOUND
    if(  $singleAddress['data'] ==  false ){
        return $this->customResponse->is400Response($response, $this->generateServerResponse(400,   "The address cannot be found") );
    }

    // VERIFY IF IT IS USER'S ADDRESS
    if(  $singleAddress['data']['homeowner_id'] !==  $userID ){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   "The user does not have access to this address") );
    }

    $result ="";
    // DELETE address
    $result = $this->file->deleteAddress($userID, $home_id );
    // Error handling
    if(  $result['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $result['data']) );
    }


    // // // Return information needed for add project
    return $this->customResponse->is200Response($response,  $result);

    // FOR DEBUGGING PURPOSES
    //    return $this->customResponse->is200Response($response,  $userID);
    // return $this->customResponse->is200Response($response,  "This route works");
}






// -----------------------------
// DEC 12


public function updateName(Request $request,Response $response){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // Validate Parameters and check if empty
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "first_name"=>v::notEmpty(),
            "last_name"=>v::notEmpty()
        ]);
    
        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }
    
    // Grab parameters
    $first_name = CustomRequestHandler::getParam($request,"first_name");
    $last_name = CustomRequestHandler::getParam($request,"last_name");

    $result ="";
    // Update Name
    $result = $this->file->updateUserName($userID, $first_name, $last_name);
    // Error handling
    if(  $result['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $result['data']) );
    }


    // // // // Return information needed for add project
    return $this->customResponse->is200Response($response,  $result);

    // FOR DEBUGGING PURPOSES
    //    return $this->customResponse->is200Response($response,  $userID);
    // return $this->customResponse->is200Response($response,  "This route works");
}




public function saveProfilePicLocation(Request $request,Response $response){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // ENSURE THAT FILE NAME AND FILE PATH ARE NOT BLANK
    // Validate Parameters and check if empty
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "file_location"=>v::notEmpty(),
            "newFileName"=>v::notEmpty()
        ]);
    
        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

    // GET FILE NAME AND FILE PATH
    // Grab parameters
    $file_location = CustomRequestHandler::getParam($request,"file_location");
    $newFileName = CustomRequestHandler::getParam($request,"newFileName");
    $filepath = $file_location.$newFileName;

    // Save into Database
    $result = "";
    $result = $this->file->saveProfilePicFileLocation($userID, $filepath);
    // Error handling
    if(  $result['success'] !== true){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401,   $result['data']) );
    }

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $filepath );

    // For Debugging purposes
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}





public function changePassword(Request $request,Response $response){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // VALIDATE AND MAKE SURE FEILDS ARE NOT BLANK
        // Check if empty
        $this->validator->validate($request,[
            // Check if empty
            "current_pass"=>v::notEmpty(),
            "new_pass"=>v::notEmpty(),
            "confirm_pass"=>v::notEmpty()
        ]);
    
        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Check new pass if above 8 characters
        $this->validator->validate($request,[
            // Check if empty
            "new_pass"=>v::length(8, null),
        ]);
    
        // Return Validation Errors
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, $responseMessage));
        }

        // Get the Values
        // Grab parameters
        $current_pass = CustomRequestHandler::getParam($request,"current_pass");
        $new_pass = CustomRequestHandler::getParam($request,"new_pass");
        $confirm_pass = CustomRequestHandler::getParam($request,"confirm_pass");

        // Check if new pass matches confirm pass
        if($new_pass != $confirm_pass){
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, "New password must match the confirm password field."));
        }

        // Retrieve current password from DB (user object)
        $userObj = $this->user->getUserByID($userID);
        // Error handling
        if(  $userObj['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $userObj['data']) );
        }

        // Check if current password matches the current password in db
        $isMatch = password_verify($current_pass, $userObj['data']['password']);
        if(!$isMatch){
            return $this->customResponse->is400Response($response, $this->generateServerResponse(400,   "Incorrect password. Please re-enter your current password.") );
        }

        // Check if new pass matches old pass, if it does return new password cannot be the same as old pass
        $isOld = password_verify($new_pass, $userObj['data']['password']);
        if($isOld){
            return $this->customResponse->is400Response($response, $this->generateServerResponse(400,   "Your new password cannot be the same as your old password.") );
        }
        
        // Save new password
        $result = "";
        $result = $this->user->changePassword($userID, $new_pass);
        if( $result['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $result['data']) );
        }


    // Return information needed for personal info page
     return $this->customResponse->is200Response($response,  $result);
    

    // For debugging purposes
    // return $this->customResponse->is200Response($response,  $isMatch);
    // return $this->customResponse->is200Response($response,  $userObj);
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}



public function changePhoneVerify(Request $request,Response $response){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

        // make sure phone and password is not empty
        $this->validator->validate($request,[
            // Check if empty
            "phone"=>v::notEmpty(),
            "phone_pass"=>v::notEmpty()
        ]);

        // SAVE DATA IN VARIABLES
        $phone = CustomRequestHandler::getParam($request,"phone");
        $current_pass = CustomRequestHandler::getParam($request,"phone_pass");

        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // Check if user exists in the database via phone number
        // Get the user object associated with phone number
        $userObject = $this->user->getUserAccountsByPhone($phone);
        if($userObject["data"] != false){
            // there is no user associated with this phone number
            return $this->customResponse->is400Response($response,  "There is a user associated with this phone number");
        } 

        // Check if passwords match
        // Retrieve current password from DB (user object)
        $userObj = $this->user->getUserByID($userID);
        // Error handling
        if(  $userObj['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $userObj['data']) );
        }

        // Check if current password matches the current password in db
        $isMatch = password_verify($current_pass, $userObj['data']['password']);
        if(!$isMatch){
            return $this->customResponse->is400Response($response, $this->generateServerResponse(400,   "Incorrect password. Please re-enter your current password.") );
        }

        $result = [];
        $result["success"] = true;
        $result["data"] = "You may now proceed with the SMS verification";


    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,   $result  );

    // For Debugging purposes
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}


// Only update the phone number once it has gone through initial verification, then SMS verification
public function updatePhoneNumber(Request $request,Response $response){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // make sure phone and password is not empty
    $this->validator->validate($request,[
        // Check if empty
        "phone"=>v::notEmpty()
    ]);

    // SAVE DATA IN VARIABLES
    $phone = CustomRequestHandler::getParam($request,"phone");

    // Returns a response when validator detects a rule breach
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    // Check if user exists in the database via phone number
    // Get the user object associated with phone number
    $userObject = $this->user->getUserAccountsByPhone($phone);
    if($userObject["data"] != false){
        // there is no user associated with this phone number
        return $this->customResponse->is400Response($response,  "There is a user associated with this phone number");
    } 

    // Update the phone number in the database
    $result = "";
    $result = $this->user->updateNewPhone($userID, $phone);
    // Error handling
    if(   $result['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,    $result['data']) );
    }

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $result);

    // For Debugging purposes
    // return $this->customResponse->is200Response($response,  $userObject );
    // return $this->customResponse->is200Response($response,  $userID );
    // return $this->customResponse->is200Response($response,  "This route works");
}



public function getHomeheroes(Request $request,Response $response){
    // Get all workers
    $workers = $this->file->getAllWorkers();
    // Error Handling
    if(   $workers['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,    $workers['data']) );
    }
    $wd = $workers['data'];

    // Get All City Preferences per worker
    $cityPer_worker = $this->file->cityPreferencePerWorker();
    // Error Handling
    if(   $cityPer_worker['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,    $cityPer_worker['data']) );
    }
    $cpw = $cityPer_worker["data"];

    // Get All Skills per worker
    $skillset_per_worker = $this->file->skillsetPerWorker();
    // Error Handling
    if(  $skillset_per_worker['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,    $skillset_per_worker['data']) );
    }
    $spw = $skillset_per_worker["data"];

    
    // GET PROFILE PIC ADDRESS PER WORKER
    $picture_per_worker = $this->file->profilepicPerWorker();
    // Error Handling
    if( $picture_per_worker['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,    $picture_per_worker['data']) );
    }
    $pppw = $picture_per_worker["data"];


    $formData = [];

    for($x = 0; $x < count($wd); $x++){
        $data = [];
        $data['worker_info'] = $wd[$x];
        $data['city_info'] = null;
        $data['skillset_info'] = null;
        $data['profile_pic'] = false;
        // I know this is not the best code since it is ON^2, but it is the only solution I can think of
        for($y = 0; $y < count( $cpw); $y++){
            if($cpw[$y]['worker_id'] == $wd[$x]["user_id"]){
                $data['city_info'] = $cpw[$y]["cities"];
            }
        }
        // Seperate loops cause each has different counts
        for($y = 0; $y < count( $spw); $y++){
            if($spw[$y]['worker_id'] == $wd[$x]["user_id"]){
                $data['skillset_info'] = $spw[$y]["skills"];
            }
        }
        for($y = 0; $y < count( $pppw); $y++){
            if($pppw[$y]['user_id'] == $wd[$x]["user_id"]){
                $data['profile_pic'] = $pppw[$y]["file_path"];
            }
        }
        array_push($formData, $data);
    }


    return $this->customResponse->is200Response($response, $formData );

    // For Debugging Purposes 
    // return $this->customResponse->is200Response($response,  "This route works");
}


public function getUsersProjects(Request $request,Response $response){
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

    // send data back
    $data = [];
    $data['ongoingJobPosts'] = $ongoingProj['data'];

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response, $data);
}



public function sendProjectToWorker(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token);

    // Error handling
    if(is_array( $userID) && array_key_exists("status", $userID)){
        return $this->customResponse->is401Response($response, $userID);
    }

    // Validate & Grab the variables needed for the query 
    // make user has selected a project ID
    $this->validator->validate($request,[
        // Check if empty
        "project_id"=>v::notEmpty()
    ]);

    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    // SAVE DATA IN VARIABLES
    $project_id = CustomRequestHandler::getParam($request,"project_id");
    $worker_ID = $args['workerID'];

    // Validate if the post belongs to the user
    $jp_res = $this->file->getSingleJobPost($project_id);
    // Error handling
    if(   $jp_res['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $jp_res['data']) );
    }
    // check if found
    if(   $jp_res['data'] == false){
        return $this->customResponse->is404Response($response, $this->generateServerResponse(404, "The post was not found. Please try again") );
    }
    // check if users post
    if(   $jp_res['data']['homeowner_id'] != $userID){
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "The user does not have access to this post"));
    }


    $formData = [];
    $formData['hasbeen_Notified_before'] = false;
    $formData['notification_status'] = null;
    // $formData['isAvailable'] = true;
    // $formData['error'] = "";

    // sCHEDULE MATCH IS
    // DISCONTINUED FOR NOW
    // 
    // // Check worker schedule
    // $worker_schedule = $this->file->getWorkerSchedulePreference( $worker_ID);
    // // Error handling
    // if($worker_schedule['success'] !== true){
    //     return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $worker_schedule['data']) );
    // }
    // // if worker is does not have schedule preference 7 notification time is anytime
    // //  also if cannot find schedule (The worker can add a schedule preference anytime)
    // $wSched = $worker_schedule['data'] ;
    // if($wSched == false || ($wSched['notice_time'] == "Anytime" && $wSched['has_schedule_preference'] == 0)){
    //     $formData['isAvailable'] = true;
    // } else {
    //     // break down the worker's schedule, convert into date time for comparison
    //     $noticeTime = $wSched['notice_time'];
    //     $p_sched = $jp_res['data']['preferred_date_time'];
    //     $jobSched = date_create($p_sched);
    //     $workerSched = date_create("Y-m-d");
    //     $today = date_create("Y-m-d");

    //     $isWithinNotice = true;
    //     $isWithinSchedule = true;
    //     $daysDifference = null;
    //     // Check if it is within the notice time of worker
    //     if($noticeTime !== "Anytime"){
    //         $daysOffset_String = "+$noticeTime days";
    //         try{

    //             $leadTimePreference=date_create('y:m:d', strtotime( $preference_string));
    //             // $daysDifference=(new DateTime("Y-m-d"))->diff($leadTimePreference)->days;
    //             // if($leadTimePreference < $jobSched || $jobSched < $leadTimePreference){
    //             //     $isWithinSchedule = false;
    //             // }
    //         } catch(Exception $e) {
    //             $isWithinNotice = true;
    //             $formData['error'] = $formData['error'].", ".$e->getMessage();
    //         }
    //     }
    //     // Check if it the day off of the worker
    //         // Get the day of the schedule
            
    //     // Check if it is within the schedule time period of the worker
    // }


    // Check if the worker has already been notified by the homeowner of this project
    // if it is in the DB then return the project data, otherwise save it into the Database
    $hasNotified = $this->file->hasWorkerBeenNotifiedOfProject($userID,  $worker_ID,  $project_id);
    // Error handling
    if(    $hasNotified['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $hasNotified['data']) );
    }
    

    if($hasNotified['data'] == false){
        // Save into the database
        $savedResult = $this->file->saveHomeownerNotifcation($userID,  $worker_ID,  $project_id);
        // Error handling
        if($savedResult['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $savedResult['data']) );
        }
        $formData['hasbeen_Notified_before'] = false;
        $formData['notification_status'] = 'Sent notification to worker';
    } else {
        // Pull from the database
        $statusResult = $this->file->checkHomeownerNotifcationStatus($userID,  $worker_ID,  $project_id);
        // Error handling
        if($statusResult['success'] !== true){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $statusResult['data']) );
        }
        $formData['hasbeen_Notified_before'] = true; 
        // If it is not found in the worker declined table, that means the worker has not responded to it yet.
        $formData['notification_status'] = $statusResult['data']==false?'Pending response from worker':'Declined by worker. Please try another worker.';
    }

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $formData);

    // For Debugging purposes  $jp_res
    // return $this->customResponse->is200Response($response,   $worker_schedule);
    // return $this->customResponse->is200Response($response,     $daysDifference);
    // return $this->customResponse->is200Response($response,   $jp_res['data']['preferred_date_time']);
    // return $this->customResponse->is200Response($response, $userID);
    // return $this->customResponse->is200Response($response,  "This route works".$args['workerID']);
}





public function getServiceAreas(Request $request,Response $response){

    // simple Call for all cities
    $cities = $this->file->getCities();
    // Error handling
    if(    $cities['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $cities['data']) );
    }

    // simple Call for all barangays
    $barangays = $this->file->getBarangays();
    // Error handling
    if(    $barangays['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $barangays['data']) );
    }

    $formData = [];
    $formData['cities'] = $cities['data'];
    $formData['barangays'] = $barangays['data'];

    $formData['All_Areas'] = [];

    for($x = 0 ; $x < count($formData['cities']) ; $x++){
        $obj = [];
        $obj['city'] = $formData['cities'][$x]['city_name'];
        $obj['barangays'] = [];

        for($y = 0; $y < count($formData['barangays']);$y++){
            if($formData['cities'][$x]['id'] == $formData['barangays'][$y]['city_id']){
                array_push($obj['barangays'], $formData['barangays'][$y]['barangay_name']);
            }

        }
        array_push($formData['All_Areas'], $obj);
    }

    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $formData);

    // For debugging purposes
    // return $this->customResponse->is200Response($response,  "This route works");
}


public function getProjectTypes(Request $request,Response $response){

    // simple Call for all project Categories
    $categories = $this->file->getProjectCategories();
    // Error handling
    if(    $categories['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $categories['data']) );
    }

    // simple Call for all project Types
    $projectTypes = $this->file->getProjectTypes();
    // Error handling
    if(    $projectTypes['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,  $projectTypes['data']) );
    }

    $formData = [];
    $formData['categories'] = $categories['data'];
    $formData['project_types'] = $projectTypes['data'];

    $formData['All_Services'] = [];

    for($x = 0 ; $x < count($formData['categories']) ; $x++){
        $obj = [];
        $obj['category'] = $formData['categories'][$x]['expertise'];
        $obj['subcategory'] = [];

        for($y = 0; $y < count($formData['project_types']);$y++){
            if($formData['categories'][$x]['id'] == $formData['project_types'][$y]['expertise']){
                array_push($obj['subcategory'], $formData['project_types'][$y]['type']);
            }

        }
        array_push($formData['All_Services'], $obj);
    }


    // Return information needed for personal info page
    return $this->customResponse->is200Response($response,  $formData);

    // For debugging purposes
    // return $this->customResponse->is200Response($response,  "This route works");
}

public function getSingleProjectW(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Catch the response, on success it is an ID, on fail it has status and message
    $userID = $this->GET_USER_ID_FROM_TOKEN($bearer_token, 2);

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
    if($singleJobPost['data']['preferred_date_time'] == null){
        return $this->customResponse->is401Response($response,$this->generateServerResponse(500, "500: Somethign went wrong, please try again."));
    }
    $postDate = $singleJobPost['data']['preferred_date_time'];
    $dateToday =  date("Y-m-d h:m:s") ;


    // Get Single Job Order Associated with post
    $singleJobOrder =  $this->file->getSingleJobOrder($jobPostID);
    // Error handling 
    if( $singleJobOrder['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $singleJobOrder['data']) );
    }
    $jo =  $singleJobOrder['data'];

    $postDate = $singleJobPost['data']['preferred_date_time'];
    $dateToday =  date("Y-m-d h:m:s") ;

    $singleBill = null;
    $singleRating = null;
    if(  $jo !== false ){
        // Check if it is the correct worker
        if($jo['worker_id'] != $userID){
            return $this->customResponse->is500Response($response,$this->generateServerResponse(401, "401: This job order is not assigned to you"));
        }

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
    } else {
        // Check if the job post is expired
        if($dateToday > $postDate){
            return $this->customResponse->is500Response($response, $this->generateServerResponse(401, "401: Job Post has already expired") );
        }
    }

    // send data back
    $data = [];
    $data['singleJobPost'] = $singleJobPost['data'];
    $data['singleJobOrder'] = $jo;
    $data['singleBill'] = $singleBill == null ? false : $singleBill['data'];
    $data['singleReview'] = $singleRating == null ? false : $singleRating['data'] ;

    //$singleJobPost['data']['preferred_date_time']


    // Return information needed for personal info page
    return $this->customResponse->is200Response($response, $data);
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