<?php


namespace App\Controllers;

use App\Models\User;
use App\Models\Support;
use App\Models\SupportTicket;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SupportAgentController
{
    protected  $user;

    protected  $customResponse;

    protected $supportAgent;

    protected $supportTicket;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->supportAgent = new Support();

        $this->supportTicket = new SupportTicket();

        $this->validator = new Validator();

        $this->user = new User();
    }

    private function generateServerResponse($status, $message){
        $response = [];
        $response['status'] = $status;
        $response['message'] = $message;
        return $response;
    }

    public function test(Request $request,Response $response){
        return $this->customResponse->is200Response($response,  "This route works");
    }

    public function getTicketDashboard(Request $request,Response $response){
        // Get Email & Role Type
        // Server side validation using Respect Validation library
        // declare a group of rules ex. if empty, equal to etc.
        $this->validator->validate($request,[
            // Check if empty
            "email"=>v::notEmpty(),
        ]);

        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // CHECK IF USER ID & ROLE TYPE IS PROVIDED (FOR ADMIN & SUP USERS)
        // IF ADMIN USER CHECK IF ROLE VERIFIED
        // Store Params
        $email = CustomRequestHandler::getParam($request,"email");

        // Get user ID with email
        $account = $this->supportAgent->getSupportAccount($email);

        // Check for query error
        if($account['success'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        // Check if email is found
        if($account['data'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is404Response($response,"Email not found");
        }

        // Get user account by ID
        $userID = $account['data']['id'];
        $role = $account['data']['role_type'];

        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Extract token by omitting "Bearer"
        $jwt = substr(trim($bearer_token),9);

        // Decode token to get user ID
        // Verify Token
        $result =  GenerateTokenController::AuthenticateUserID($jwt, $userID);

        if($result['status'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $result['message']) );
        }


        // New Tickets
        $newTickets = $this->supportTicket->get_Tickets(1,true,null,$role);
        // Check for query error
        if($newTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$newTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        // Ongoing Tickets
        $ongoingTickets = $this->supportTicket->get_Tickets(2,true,$userID);
        // Check for query error
        if($ongoingTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$ongoingTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        // Resolved Tickets
        $resolvedTickets = $this->supportTicket->get_Tickets(3,true,$userID);
        // Check for query error
        if($resolvedTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$resolvedTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
                
        // Anouncements
        // TBD

        $resData['new_total'] = $newTickets["data"]["0"]["0"];
        $resData['ongoing_total'] = $ongoingTickets["data"]["0"]["COUNT(*)"];
        $resData['resolved_total'] = $resolvedTickets["data"]["0"]["COUNT(*)"];
        // $resData['anouncements'] = [];

        // 
        // return $this->customResponse->is200Response($response, $newTickets);
        // return $this->customResponse->is200Response($response, $ongoingTickets);
        // return $this->customResponse->is200Response($response, $resolvedTickets);
        return $this->customResponse->is200Response($response,  $resData);
    }

    public function getMyTickets(Request $request,Response $response){
        // Get the auth header and userID to get users tickets
        // Get Email & Role Type
        // Server side validation using Respect Validation library
        // declare a group of rules ex. if empty, equal to etc.
        $this->validator->validate($request,[
            // Check if empty
            "email"=>v::notEmpty(),
            "page"=>v::notEmpty(),
            "limit"=>v::notEmpty()
        ]);

        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // CHECK IF USER ID & ROLE TYPE IS PROVIDED (FOR ADMIN & SUP USERS)
        // IF ADMIN USER CHECK IF ROLE VERIFIED
        // Store Params
        $email = CustomRequestHandler::getParam($request,"email");
        $page = CustomRequestHandler::getParam($request,"page");
        $limit = CustomRequestHandler::getParam($request,"limit");

        // Get user ID with email
        $account = $this->supportAgent->getSupportAccount($email);

        // Check for query error
        if($account['success'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        // Check if email is found
        if($account['data'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is404Response($response,"Email not found");
        }

        // Get user account by ID
        $userID = $account['data']['id'];
        $role = $account['data']['role_type'];

        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Extract token by omitting "Bearer"
        $jwt = substr(trim($bearer_token),9);

        // Decode token to get user ID
        // Verify Token
        $result =  GenerateTokenController::AuthenticateUserID($jwt, $userID);

        if($result['status'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $result['message']) );
        }

        // For pagination - determine results size
        // ex page 3 = 1-10, 11,20, 21-30 <- this page
        //  Server 0-9,10-19, 20-29
        // compute offset
        $offset = (($page-1)*$limit); // $page, $limit

        // For pagination - determine total size/pages
        // =================================================
        // Ongoing
        $totalOngoing = $this->supportTicket->get_Tickets(2,true,$userID);
            // Check for query error
            if($totalOngoing['success'] == false){
                // return $this->customResponse->is500Response($response,$totalOngoing['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
        // Completed
        $totalCompleted = $this->supportTicket->get_Tickets(3,true,$userID);
            // Check for query error
            if($totalCompleted['success'] == false){
                // return $this->customResponse->is500Response($response,$totalCompleted['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
        // Escalations
        $totalEscalations = $this->supportTicket->get_Tickets(4,true,$userID);
        // Check for query error
        if($totalEscalations['success'] == false){
            // return $this->customResponse->is500Response($response,$totalEscalations['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Transferred
        $totalTransferredTickets = $this->supportTicket->get_transferred_tickets(true,$userID,$limit,$offset);
        // Check for query error
        if($totalTransferredTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$totalTransferredTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Stale - TBD

        // For page data - determine total size/pages
        // =================================================
        // Ongoing
        $ongoingTickets = $this->supportTicket->get_Tickets(2,false,$userID,null,$limit,$offset);
        // Check for query error
        if($ongoingTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$ongoingTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        // Completed
        $completedTickets = $this->supportTicket->get_Tickets(3,false,$userID,null,$limit,$offset);
        // Check for query error
        if($completedTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$completedTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Escalations
        $escalatedTickets = $this->supportTicket->get_Tickets(4,false,$userID,null,$limit,$offset);
        // Check for query error
        if($escalatedTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$escalatedTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Transferred
        $transferredTickets = $this->supportTicket->get_transferred_tickets(false,$userID,$limit,$offset);
        // Check for query error
        if($transferredTickets['success'] == false){
            // return $this->customResponse->is500Response($response,$transferredTickets['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Stale - TBD


        $resData['ongoing_total'] = $totalOngoing["data"]["0"]["COUNT(*)"];
        $resData['completed_total'] = $totalCompleted["data"]["0"]["COUNT(*)"];
        $resData['escalated_total'] = $totalEscalations["data"]["0"]["COUNT(*)"];
        $resData['transferred_total'] = count($totalTransferredTickets["data"]) == 0 ? 0 : $totalTransferredTickets["data"]["0"]["COUNT(*)"];
        $resData['ongoing_tickets'] = $ongoingTickets["data"];
        $resData['completed_tickets'] = $completedTickets["data"];
        $resData['escalated_tickets'] = $escalatedTickets["data"];
        $resData['transferredTickets'] = $transferredTickets["data"];

        return $this->customResponse->is200Response($response,  $resData);
    }


// -----------------------------------
// Get Codes
// -----------------------------------
    public function getMyCodes(Request $request,Response $response){
    // -----------------------------------
    // Get Necessary variables and params
    // -----------------------------------
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Get Agent Email for validation
        $this->validator->validate($request,[
            // Check if empty
            "email"=>v::notEmpty()
        ]);

        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // Store Params
        $email = CustomRequestHandler::getParam($request,"email");
        $supervisor_id = CustomRequestHandler::getParam($request,"supervisor_id");

        // Get Support User Account
    $account = $this->supportAgent->getSupportAccount($email);

    // Check for query error
    if($account['success'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }

    // Check if email is found
    if($account['data'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is404Response($response,"Email not found");
    }

    // Get user account by ID
    $userID = $account['data']['id'];
    $userRole = $account['data']['role_type'];

        // Check if correct role
        if( $userRole != 4 && $userRole != 5 && $userRole != 6 && $userRole != 7 ){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is401Response($response,"Unauthorized Access: Only Supervisors, Managers and Admins are allowed to access this resource.");
        }


    $userAcc = $this->user->getUserByID($userID);

    // Check for query error
    if($userAcc['success'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }

    // Check if user is found
    if($userAcc['data'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is404Response($response,"User not found");
    }

// -----------------------------------
// Auth Agent Information
// -----------------------------------
$auth_agent_result = $this->authenticate_agent($bearer_token, $userID);
if($auth_agent_result['success'] != 200){
    return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
}

    // If Everything is correct get the owner's code
    $permissions_owner = $userRole == 7 ? $supervisor_id : $userID;
    $codeArr_reformatted = [];
    $codesArr = [];

    // Based on role get the needed array
    switch($userRole){
        case 7:
            // Manager
            break;
        case 4:
            // Supervisor
            $codesRes = $this->supportAgent->get_permission_codes($permissions_owner, 1);
            if($codesRes['success'] != 200){
                // return $this->customResponse->is500Response($response,$codesRes['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
            $codesArr = $codesRes['data'];
            break;
        default:
            return $this->customResponse->is401Response($response,"Unauthorized access: Only supervisors, managers and admin are allowed to access this resource. Please log in to an authorized account.");
            break;
    }



        for($x=0;$x<count($codesArr);$x++){
            $plain = openssl_decrypt($codesArr[$x]["override_code"], "AES-128-ECB", "WQu0rd4T");
            // $codesArr[$x]["override_code"] = $plain;
            $codeArr_reformatted[($userRole == 7 ? $supervisor_id."_" : "DEFAULT_").$codesArr[$x]['permissions_id']] =  $plain;
        }

        $resData = [];
        $resData['codesRes'] =   $codeArr_reformatted;

        return $this->customResponse->is200Response($response,  $resData);
}

public function getSupReason(Request $request,Response $response){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty()
    ]);

    // Returns a response when validator detects a rule breach
    if($this->validator->failed())
    {
        $responseMessage = $this->validator->errors;
        return $this->customResponse->is400Response($response,$responseMessage);
    }

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $supervisor_id = CustomRequestHandler::getParam($request,"supervisor_id");

        // Get Support User Account
    $account = $this->supportAgent->getSupportAccount($email);

    // Check for query error
    if($account['success'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }

    // Check if email is found
    if($account['data'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is404Response($response,"Email not found");
    }

    // Get user account by ID
    $userID = $account['data']['id'];
    $userRole = $account['data']['role_type'];

        // Check if correct role
        if( $userRole == 2 && $userRole == 1  ){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is401Response($response,"Unauthorized Access: Only HomeHero Support Staff is allowed to access this resource.");
        }


    $userAcc = $this->user->getUserByID($userID);

    // Check for query error
    if($userAcc['success'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }

    // Check if user is found
    if($userAcc['data'] == false){
        // return $this->customResponse->is500Response($response,$account['data']);
        return $this->customResponse->is404Response($response,"User not found");
    }

    // -----------------------------------
    // Auth Agent Information
    // -----------------------------------
    $auth_agent_result = $this->authenticate_agent($bearer_token, $userID);
    if($auth_agent_result['success'] != 200){
    return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
    }


    // If Everything is correct, get the list of transfer reasons. & Get the Sup
        // Get Sup Name
        // Get Support User Account
        $full_name = $this->supportAgent->get_user_name($userID);

        // Check for query error
        if($full_name['success'] == false){
            // return $this->customResponse->is500Response($response,$full_name['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        // Get Trans Reasons
        $transReasons = $this->supportAgent->get_trans_reasons();

        // Check for query error
        if($transReasons['success'] == false){
            // return $this->customResponse->is500Response($response,$transReasons['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

    $resData = [];
    $resData['supID'] = $account['data']['supervisor_id'];
    $resData['sup_name'] = isset( $full_name['data']) ?   $full_name['data']['full_name']  : "";
    $resData['transReasons'] = $transReasons['data'];

    // // Return information needed for personal info page
    // return $this->customResponse->is200Response($response,  $userID );
    return $this->customResponse->is200Response($response,  $resData);
}






























// Helper function to perform ticket validation
// params: ticket_ID, supportTicket obj
// returns: object with keys data & success
private function return_server_response($r_res,  $r_message = "",$r_code = 200, $r_data=null){
    $formatted_res = [];
    $formatted_res['status'] = $r_code;
    $formatted_res['message'] = $r_message;
    $formatted_res['data'] = $r_data;
    switch($r_code){
        case 500:
            return $this->customResponse->is500Response($r_res,  $formatted_res);
        break; 
        case 404:
            return $this->customResponse->is404Response($r_res,  $formatted_res);
        break;  
        case 400:
            return $this->customResponse->is400Response($r_res,  $formatted_res);
        break; 
        default:
            return $this->customResponse->is200Response($r_res,  $formatted_res);
        break;
    }
}


// Helper function to authenticate agent
// params: email, supportTicket obj
// returns: object with keys data & success
public function authenticate_agent($a_bearer_token,$user_ID){
    $retVal = [];
    // Extract token by omitting "Bearer"
    $jwt = substr(trim($a_bearer_token),9);

    // Decode token to get user ID
    $jwt_result =  GenerateTokenController::AuthenticateUserID($jwt, $user_ID);

    if($jwt_result['status'] !== true){
        $retVal['success'] = 401;
        // $retVal['data'] = $jwt_result['data'];
        $retVal['error'] = $jwt_result['message'];
        return $retVal;
    }

    $retVal['success'] = 200;
    $retVal['error'] =  [];
    $retVal['data'] =  null;

    return $retVal;
}

}

