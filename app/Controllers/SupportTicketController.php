<?php

namespace App\Controllers;

use App\Models\SupportTicket;
use App\Models\Support;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use App\Db\DB;

class SupportTicketController
{
    protected  $customResponse;

    protected  $supportTicket;
    
    protected $supportAgent;

    protected  $validator;

    protected  $roleTypes;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->supportTicket = new SupportTicket();

         $this->supportAgent = new Support();

         $this->validator = new Validator();

         $this->roleSubTypes = array();

         // verification support
         $this->roleSubTypes["0"] = "0";
         $this->roleSubTypes["1"] = "1";
         $this->roleSubTypes["2"] = "1";
         $this->roleSubTypes["3"] = "1";
         // Customer support
         $this->roleSubTypes["4"] = "2";
         $this->roleSubTypes["5"] = "2";
         $this->roleSubTypes["6"] = "2";
         $this->roleSubTypes["7"] = "2";
         $this->roleSubTypes["8"] = "2";
         $this->roleSubTypes["9"] = "2";
         // Technical support
         $this->roleSubTypes["10"] = "3";
         $this->roleSubTypes["11"] = "3";
         $this->roleSubTypes["12"] = "3";
         $this->roleSubTypes["13"] = "3";
         $this->roleSubTypes["14"] = "3";
         $this->roleSubTypes["15"] = "3";
         $this->roleSubTypes["16"] = "3";
    }

    private function generateServerResponse($status, $message){
        $response = [];
        $response['status'] = $status;
        $response['message'] = $message;
        return $response;
    }



    // creates a support ticket into the database
    public function createTicket(Request $request,Response $response)
    {
        //Sanitize the Data with the Validator
        $this->validator->validate($request,[
            "author"=>v::notEmpty(),
            "subcategory"=>v::notEmpty(),
            "authorDescription"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        // Create Ticket
        $ModelResponse = $this->supportTicket->createTicket(
            CustomRequestHandler::getParam($request,"author"),
            CustomRequestHandler::getParam($request,"subcategory"),
            CustomRequestHandler::getParam($request,"authorDescription"),
            CustomRequestHandler::getParam($request,"totalImages")
        );

        $this->customResponse->is200Response($response,  "Ticket Successfully created");
    }


    // gets all tickets
    public function getAll(Request $request,Response $response)
    {
        $ModelResponse = $this->supportTicket->get_All();

        $this->customResponse->is200Response($response,  $ModelResponse);
    }

    
    // gets all user's specified tickets
    public function getSingle(Request $request,Response $response, array $args)
    {
        $ModelResponse = $this->supportTicket->get_All($args['id']);

        $this->customResponse->is200Response($response,  $ModelResponse);
    }


// ===================================================================
//  April 18 2022

    // Search tickets by Type
    // ["general","id","name","author","category"]
    public function search(Request $request,Response $response, array $args){
        // constants
        $typesArr = ["general","id","name","author","category"];

        // Get Search Variables
        $limit = $args['limit'];
        $page = $args['page'];
        $type = $args['type'];
        $query = $args['keywords'];

        // Clean Variables
        $limit =  is_numeric($limit) ? ($limit > 1000 ? 1000 : $limit) : 10;
        $page = (is_numeric($page) ? $page : 1);
        $type = array_search($type, $typesArr) == FALSE ? 1 :  array_search($type, $typesArr);

        // Seperate by - or space
        $hyphen = explode( '-', $query);
        $space = explode( '+', $query);
        $ticketID = $args['keywords'];
        $name = "";

        if(count($hyphen) == 2){
            $ticketID = $hyphen[1];
        }

        if(is_numeric(0+$ticketID)){
            $ticketID = intval($ticketID);
        }

        $resData['success'] = "";
        $resData['data'] = "";
        // searchTicketNo($id = null, $category = null, $agent_name = null, $author_name = null, $limit=1000, $offset=0)
        
        function search($s_userid = null, $s_id = null, $s_category = null, $s_agent_name = null, $s_author_name = null, $s_limit=1000, $s_offset=0, $s_sticketobj) {
            return $s_sticketobj->search($s_userid,$s_id,$s_category,$s_agent_name,$s_author_name,$s_limit,$s_offset);
        }

        switch($type){
            case 0:
                // Search General
            break;
                // Search by Ticket ID
            case 2:
                // Search by agent name
                break;
            case 3:
                // Search by author name
                break;
            case 4:
                // Search by category
                break;
            default:
                // Search by Ticket ID
                $resData = search(null,$ticketID,null,null,null,$limit,$page,$this->supportTicket);
            break;
        } 

        // Check for query error
        if($resData['success'] == false){
            // return $this->customResponse->is500Response($response,$resData['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Check for empty error
        if($resData['success'] == ""){
            return $this->customResponse->is500Response($response,"SERVER ERROR: Returns No Success type");
        }

        // Get the value of args for the search keywords
        return $this->customResponse->is200Response($response, $resData['data']);
    }





// ===================================================================
//  April 29 2022

// gets all info pn specified ticket
public function getAllTickets(Request $request,Response $response, array $args)
{
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
        // New
            $totalNew = $this->supportTicket->get_Tickets(1,true,null,$role);
            // Check for query error
            if($totalNew['success'] == false){
                // return $this->customResponse->is500Response($response,$totalNew['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
        // Ongoing
        $totalOngoing = $this->supportTicket->get_Tickets(2,true,null,$role);
        // Check for query error
        if($totalNew['success'] == false){
            // return $this->customResponse->is500Response($response,$totalOngoing['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }




        // For page data - determine total size/pages
        // =================================================
        // Get necessary info
            // New
            $newTickets = $this->supportTicket->get_Tickets(1,false,null,$role,$limit,$offset);
            // Check for query error
            if($newTickets['success'] == false){
                // return $this->customResponse->is500Response($response,$ongoingTickets['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
            // Ongoing
            $ongoingTickets = $this->supportTicket->get_Tickets(2,false,null,$role,$limit,$offset);
            // Check for query error
            if($ongoingTickets['success'] == false){
                // return $this->customResponse->is500Response($response,$ongoingTickets['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
            // Completed
            $completedTickets = $this->supportTicket->get_Tickets(3,false,null,$role,$limit,$offset);
            // Check for query error
            if($completedTickets['success'] == false){
                // return $this->customResponse->is500Response($response,$completedTickets['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
            // Escalations
            // Transfers


        $resData = [];
        $resData["new_total"] = $totalNew["data"][0]["COUNT(*)"];
        $resData["ongoing_total"] = $totalOngoing["data"][0]["COUNT(*)"];
        $resData["new"] = $newTickets["data"];
        $resData["ongoing"] = $ongoingTickets["data"];



    // verify if user is allowed to access this ticket
    return $this->customResponse->is200Response($response,  $resData);
}



// ===================================================================
//  May 4 2022

// assigns a ticket to an agent
public function assignTicket(Request $request,Response $response, array $args)
{

    // Get ticket parameter for ticket information
    $ticket_id = $args['id'];

    // Extract from db
    $base_info = $this->supportTicket->get_ticket_base_info($ticket_id);

    // Check for query error
    if($base_info['success'] == false){
        // return $this->customResponse->is500Response($response,$base_info['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }

    // Check if not found
    if($base_info['data'] == false){
        // return $this->customResponse->is500Response($response,$base_info['data']);
        return $this->customResponse->is404Response($response,"Ticket Not found.");
    }

    // Only on new ticket but otherwise if it is a transfer ignore (Fix later)
    // Check if ticket has already been assigned
    if($base_info["data"]["assigned_agent"]){
        // return $this->customResponse->is500Response($response,$base_info['data']);
        return $this->customResponse->is401Response($response,"Ticket already assgined to another agent.");
    }

    // Check if the user is allowed to assign the ticket to themselves
    // Get role data
    // Get Email
    // Server side validation using Respect Validation library
    // declare a group of rules ex. if empty, equal to etc.
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

    // VERIFY ACCOUNT & CHECK ROLE TYPE
    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $type = CustomRequestHandler::getParam($request,"type");
    $transferTo = CustomRequestHandler::getParam($request,"type");

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
        return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token unrecognized. Email not found. Please sign into your account."));
    }

    // Get user account by ID & get role type
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

    // After the authentication steps above -> put this in a function or constant
    // Check the role type with the ticket type
        // Registration/verification agent
        $roleSubTypes["0"] = "0";
        $roleSubTypes["1"] = "1";
        $roleSubTypes["2"] = "1";
        $roleSubTypes["3"] = "1";
        // Customer support
        $roleSubTypes["4"] = "2";
        $roleSubTypes["5"] = "2";
        $roleSubTypes["6"] = "2";
        $roleSubTypes["7"] = "2";
        $roleSubTypes["8"] = "2";
        $roleSubTypes["9"] = "2";
        // Technical support
        $roleSubTypes["10"] = "3";
        $roleSubTypes["11"] = "3";
        $roleSubTypes["12"] = "3";
        $roleSubTypes["13"] = "3";
        $roleSubTypes["14"] = "3";
        $roleSubTypes["15"] = "3";
        $roleSubTypes["16"] = "3";
        $roleFilter = $base_info["data"]["issue_id"] != null && is_numeric($base_info["data"]["issue_id"]) 
        && $base_info["data"]["issue_id"] > 0 && $base_info["data"]["issue_id"] < 17 
        ? number_format($base_info["data"]["issue_id"],0,"","")
        : "0";

    if($roleSubTypes[$roleFilter]!=$role && $role != 4 && $role != 6 && $role !=5){
        return $this->customResponse->is401Response($response,  "This ticket is out of scope for your current role. Please select another ticket.");
    }

    // $transferTo - future check if this agent can handle transfer
    // action types:  2-assigned, 4-transferred, 5-escalated
    $actionID = $type == null ? 2 : (is_numeric($type) && ($type == 2 || $type == 4 || $type == 5) ? $type : 2);
    $messages = [];
    $messages["2"] = "AGENT #".$userID." ACCEPTED TICKET";
    $messages["4"] = "AGENT #".$userID." TRANSFERRED TICKET TO AGENT #"; 
    $messages["5"] = "AGENT #".$userID." ESCALATED TICKET TO SUPERVISOR #";

    // If all is good then assign the ticket to the agent
    // Get user ID with email
    $assign_res = $this->supportTicket->assign_ticket($userID,$ticket_id,$actionID,$messages[$actionID],2);

    if($assign_res['success'] !== true){
        // return $this->customResponse->is500Response($response,$assign_res['data']);
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500, "Something went wrong. Please try again.") );
    }

    $res = [];
    $res["message"] = "Ticket Assignment Success";
    $res["status"] = 200;

    return $this->customResponse->is200Response($response,  $res);

    
    // Notes, neeeds adjustment/validation for the following scenarios
    /*
        agent transfers to another agent
        agent escalates to supervisor
        supervisor/admin accepts ticket (new)
        supervisor/admin accepts ticket (old)
        supervisor/admin transfers ticket to another agent
    */
    // Done 
    /*
        Agent accepts a new ticket
    */
}




// ===================================================================
//  May 6 2022

// assigns a ticket to an agent
public function updateWorkerRegistration(Request $request,Response $response, array $args)
{
    // -----------------------------------
    // Get Necessary variables and params
    // -----------------------------------
    // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
        $ticket_id = $args['id'];

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty(),
        "type"=>v::notEmpty(),
    ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        $this->validator->validate($request,[
            // Check if empty
            "type"=>v::between(1, 4),
        ]);
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        // Store Params
        $comment = CustomRequestHandler::getParam($request,"comment");
        $email = CustomRequestHandler::getParam($request,"email");
        $type = CustomRequestHandler::getParam($request,"type");

    // -----------------------------------
    // Get Agent Information
    // -----------------------------------
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
            return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token & email not found. Please sign into your account."));
        }
    
        // Get user account by ID & get role type
        $agent_ID = $account['data']['id'];
        $role = $account['data']['role_type'];

    // -----------------------------------
    // Auth Agent Information
    // -----------------------------------
    $auth_agent_result = $this->authenticate_agent($bearer_token, $agent_ID);
    if($auth_agent_result['success'] != 200){
        return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
    }

    // -----------------------------------
    // Validate Ticket
    // -----------------------------------
    $validate_ticket_result = $this->validate_ticket($ticket_id,2,$agent_ID);
    if($validate_ticket_result['success'] != 200){
        return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
    }
    $base_info = $validate_ticket_result['data'];

    $resData = [];
    // $resData['base_info'] = $base_info['data'];
    $resData['ticket_ID'] = $base_info['data']['id'];

    // -----------------------------------
    // Get NBI Info of Ticket
    // -----------------------------------
    $nbi_res = $this->supportTicket->get_nbi_info($base_info["data"]["id"]);
    // Check for query error
    if( $nbi_res['success'] == false){
        // return $this->customResponse->is500Response($nbi,$his['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    $nbi_info = $nbi_res["data"];
    if(count($nbi_info) <= 0){
        return $this->customResponse->is404Response($response,"NBI Information not found. Please escalate ticket.");
    }

    $nbi_id = $nbi_info[0]["id"];
    $worker_id =  $nbi_info[0]["worker_id"];
    $ticket_id =  $base_info['data']['id'];
    $agent_ID_currrent =  $base_info['data']['assigned_agent'];

    // -----------------------------------
    // Process Ticket
    // -----------------------------------
    switch($type){
        case 1:
            // Case Approve
            // $resData['type'] = 1;

            $approveRes = $this->supportTicket->update_worker_registration($agent_ID_currrent, $ticket_id, $worker_id, $nbi_id, 1,  $comment);
            if(   $approveRes["success"] == true){
                $resData['message'] = "Registration approved successfully!";
            } else {
                return $this->return_server_response($response,"An Error occured while approving the registration. Please try again.",500);
            }
        break;
        case 2:
            // Case Disapprove
            // $resData['type'] = 2;
            $denyRes = $this->supportTicket->update_worker_registration($agent_ID_currrent, $ticket_id, $worker_id, $nbi_id, 2,  $comment);
            if(    $denyRes["success"] == true){
                $resData['message'] = "Registration denied successfully.";
            } else {
                return $this->return_server_response($response,"An Error occured while denying the registration. Please try again.",500);
            }
        break;
        case 3:
            // Case Notify
            // $resData['type'] = 3;
            /*
                has author taken action = 0, No further actions needed
                has author taken action = 1, Ticket Just Submitted
                has author taken action = 2, Agent Notified Author
                has author taken action = 3, Author Notified Agent
            */
            if($comment == null){
                return $this->return_server_response($response,"No comment provided. Please include a comment.",400);
            } else {
                $notifyRes = $this->supportTicket->comment($ticket_id, $agent_ID_currrent, $comment, 1);
                if($notifyRes["success"] == true){
                    $resData['message'] = "Customer notified and comment successfully added to the ticket.";
                } else {
                    return $this->return_server_response($response,"An Error occured while saving the comment. Please try again.",500);
                }
            }
        break;
        default:
            // Case Option is to just Comment on Ticket "Add Info"
            if($comment == null){
                return $this->return_server_response($response,"No comment provided. Please include a comment.",400);
            } else {
                $commentRes = $this->supportTicket->comment($ticket_id, $agent_ID_currrent, $comment);
                if($commentRes["success"] == true){
                    $resData['message'] = "Comment was successfully added to the ticket.";
                } else {
                    return $this->return_server_response($response,"An Error occured while saving the comment. Please try again.",500);
                }
            }
        break;
    }


    return $this->return_server_response($response,$resData['message'],200);   
}





// ===================================================================
//  May 12, 2022

// gets all info pn specified ticket
public function getInfo(Request $request,Response $response, array $args)
{
    /*
        The following is needed:
            - bearer token (from header)
            - email (from request)
            - requesting agent information (from db based on email)
            - ticket ID (from args - route URI) 

        The ff steps are performed:
            - validate bearer token & email with JWT
            - validate agent authorization through role & ownership
            - provide necessary information based on role & ownership
    */
// -----------------------------------
// Get Necessary variables and params
// -----------------------------------
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
    $ticket_id = $args['id'];

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

// -----------------------------------
// Get Agent Information
// -----------------------------------
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
            return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token & email not found. Please sign into your account."));
        }
    
        // Get user account by ID & get role type
        $agent_ID = $account['data']['id'];
        $role = $account['data']['role_type'];

// -----------------------------------
// Auth Agent Information
// -----------------------------------
        $auth_agent_result = $this->authenticate_agent($bearer_token, $agent_ID);
        if($auth_agent_result['success'] != 200){
            return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
        }

// -----------------------------------
// Validate Ticket
// -----------------------------------
        $validate_ticket_result = $this->validate_ticket($ticket_id,null,$agent_ID,$role);
        if($validate_ticket_result['success'] != 200){
            return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
        }
        $base_info = $validate_ticket_result['data'];
        $is_owner = $validate_ticket_result['is_owner'];
        $authorized = $validate_ticket_result['authorized'];

// -----------------------------------
// Get general ticket information shown for all roles
// -----------------------------------
    $id_ticket = $base_info["data"]["id"];
    // ------------------
    // Get ticket history 
    $his = $this->supportTicket->get_ticket_history($id_ticket);
    // Check for query error
    if($his['success'] == false){
        // return $this->customResponse->is500Response($response,$his['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    // ------------------
    // Get ticket comments 
    $comment_his = $this->supportTicket->get_ticket_comment_history($id_ticket);
    // Check for query error
    if($comment_his['success'] == false){
        // return $this->customResponse->is500Response($response,$comment_his['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    // ------------------
    // Get ticket assignment history (Needed for All roles of tickets)
    $transfer_his = $this->supportTicket->get_ticket_transfer_history($id_ticket);
    // Check for query error
    if($transfer_his['success'] == false){
        // return $this->customResponse->is500Response($response,$transfer_his['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }


// ----------------------------------------------------------
// Provide necessary information based on role & authorization
// ----------------------------------------------------------
        $ticket_issue = $base_info["data"]["issue_id"];
        $params = [];
        $params['ticket_ID'] =  $base_info["data"]["id"];
        $params['authorized'] =  $authorized;
        $params['ownership'] =  $is_owner;
        $detailed_info_res = null;

        // Double Check Needed Data
        if($params['ticket_ID'] == null){
            return $this->return_server_response($response,'Ticket ID Invalid or Blank', 500, null);
        }

        // Execute necessary function based on issue
        switch($ticket_issue){
            // -----------------------------------
            // Worker Registration
            case 1:
                $detailed_info_res = $this->get_worker_registration($params);
                break;
            // -----------------------------------
            // Billing Issue
            case 4:
                $detailed_info_res = $this->get_billing_issue($params);
                break;
            // -----------------------------------
            // Job Order Issue
            case 7:
                $detailed_info_res = $this->get_job_order_issue($params);
                break;
        }
        // Check for errors
        if($detailed_info_res != null && $detailed_info_res['error'] != null){
            return $this->return_server_response($response,$detailed_info_res['error'] ?? 'There was an errror in the server code in retreiving detailed information.', $detailed_info_res['status'] ?? 500, null);
        }

// -----------------------------------
// Return information
// -----------------------------------
    $resData = [];
    $resData['base_info'] = $base_info['data'];
    $resData["history"] = $his["data"];
    $resData["comments"] = $comment_his["data"];
    $resData["assignment_history"] = $transfer_his["data"];
    $resData['ownership'] = $is_owner;
    $resData['authorization'] = $authorized;
    $resData['detailed_info'] = $detailed_info_res == null ? [] : $detailed_info_res['data'];

    return $this->return_server_response($response,"This route works",200, $resData);  
}




// ===================================================================
//  May 14, 2022

// processes the billing
public function processBilling(Request $request,Response $response, array $args)
{
// -----------------------------------
// Get Necessary variables and params
// -----------------------------------
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
    $ticket_id = $args['id'];

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty(),
        "type"=>v::notEmpty()
    ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        $this->validator->validate($request,[
            // Check if empty
            "type"=>v::between(1, 4),
        ]);
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $type = CustomRequestHandler::getParam($request,"type");
    $comment = CustomRequestHandler::getParam($request,"comment");
        // Additional info for bill edit
        $payment_method = CustomRequestHandler::getParam($request,"payment_method");
        $inpt_bill_status = CustomRequestHandler::getParam($request,"inpt_bill_status");
        $fee_adjustment = CustomRequestHandler::getParam($request,"fee_adjustment");
        

// -----------------------------------
// Get Agent Information
// -----------------------------------
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
            return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token & email not found. Please sign into your account."));
        }
    
        // Get user account by ID & get role type
        $agent_ID = $account['data']['id'];
        $role = $account['data']['role_type'];

// -----------------------------------
// Auth Agent Information
// -----------------------------------
        $auth_agent_result = $this->authenticate_agent($bearer_token, $agent_ID);
        if($auth_agent_result['success'] != 200){
            return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
        }

// -----------------------------------
// Validate Ticket
// -----------------------------------
        $validate_ticket_result = $this->validate_ticket($ticket_id,2,$agent_ID,$role);
        if($validate_ticket_result['success'] != 200){
            return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
        }
        $base_info = $validate_ticket_result['data'];
        $is_owner = $validate_ticket_result['is_owner'];
        $authorized = $validate_ticket_result['authorized'];
        $ticket_id =  $base_info['data']['id'];
        $agent_ID_currrent =  $base_info['data']['assigned_agent'];

        $resData = [];
// -----------------------------------
// Process Ticket
// -----------------------------------
        switch($type){
            case 1:
                // Case Edit Bill
                // $resData["res"] = "Edit";

                $editRes = $this->supportTicket->process_bill($agent_ID_currrent, $ticket_id, $payment_method, $inpt_bill_status, $fee_adjustment, $comment);
                if($editRes["success"] == true){
                    if($editRes["data"] == null){
                        $resData['message'] = "Bill not found!";
                    } else {
                        $resData['message'] = "Bill adjusted successfully!";
                        // $resData['data'] = $editRes;
                        $resData['data'] = $editRes['data'];
                    }
                } else {
                    if(isset($editRes["err"])){
                        return $this->return_server_response($response,$editRes["data"],400);
                    } else {
                        // return $this->return_server_response($response,$editRes,500);
                        return $this->return_server_response($response,"Something went wrong when updating the bill information. Please contact administrator to check SQL syntax.",500);
                    }
                }

                break;
            case 2:
                $resData["res"] = "Cancel";
                break;
            case 3:
                $resData["res"] = "Notify";
                break;
            case 4:
                $resData["res"] = "Comment";
                break;
            case 5:
                $resData["res"] = "Close ticket";
                break;
            default:
                break;
        }



    return $this->return_server_response($response,"This route works",200, $resData);  
}

    

























































// Helper function to perform ticket validation
// params: agent_role, supportticket_issue
// returns: object with keys data & success
private function authorize_agent_role_with_ticket($agentRole, $ticket_issue){
    // Reference for list of tickets types
    /* 
        // $roleSubTypes = ["1,2,3","4,5,6,7,8,9,10,11","12,13,14,15,16"];
        // Registration/Verification, Customer Support,GEN, Technical Support
        // supervisor, admin, superadmin
    */
    $roleFilter = $ticket_issue != null && is_numeric($ticket_issue) 
    && $ticket_issue > 0 && $ticket_issue < 17 
    ? number_format($ticket_issue,0,"","")
    : "0";

    return $this->roleSubTypes[$roleFilter]!=$agentRole && $agentRole != 4 && $agentRole != 6 && $agentRole !=5 ? false : true;
}

// Helper function to perform ticket validation
// params: ticket_ID, supportTicket obj
// returns: object with keys data & success
private function validate_ticket($p_ticket_id, $p_ticket_status = null, $p_agent_ID = null, $p_agent_role = null){
    // Reference for list of roles & their authorization
    /* 
        1 - Verification (Cann access only verification tickets)
        2 - Customer Support (Cann access both csx & verification tickets)
        3 - Technical Support (Cann access only tech tickets)
        4 - Supervisor  (Can access all types)
        5 - Admin (Can access all types)
        6 - Super Admin (Can access all types)
    */

    $retVal = [];

    // Check if ticket is null, empty or not a number
    if(!isset($p_ticket_id) ||  $p_ticket_id == null || !is_numeric($p_ticket_id)){
        $retVal['success'] = 400;
        // $retVal['data'] = $p_ticket_id;
        $retVal['error'] = "Bad Request. No Ticket ID or Invalid ID has been provided.";
        return $retVal;
    }

    // Extract from db
    $base_info = $this->supportTicket->get_ticket_base_info($p_ticket_id);

    // Check for query error
    if($base_info['success'] == false){
        $retVal['success'] = 500;
        // $retVal['data'] = $base_info['data'];
        $retVal['error'] = "SQLSTATE[42000]: Syntax error or access violation: Please check your query.";
        return $retVal;
    }

    // Check if not found
    if($base_info['data'] == false){
        $retVal['success'] = 404;
        // $retVal['data'] = $base_info['data'];
        $retVal['error'] = "Ticket Not Found";
        return $retVal;
    }

    // Check if correct status
    $bi_stat = $base_info['data']['status'];
    if($p_ticket_status != null && $bi_stat != $p_ticket_status){
        $retVal['success'] = 400;
        // $retVal['data'] = $base_info['data'];
        $s_msg = ["This ticket has not been assigned to an agent yet.","This ticket is already assigned to an agent.","This ticket is already closed."];
        $retVal['error'] = $s_msg[$bi_stat-1];
        return $retVal;
    }

    $retVal['is_owner'] =  false;
    // Check if this agent is the owner
    if($p_agent_ID != null && $p_agent_ID == $base_info['data']['assigned_agent']){
        $retVal['is_owner'] =  true;
    } 
    if($base_info['data']['assigned_agent'] == null){
        $retVal['is_owner'] =  null;
    } 

    // Check if it is the authorized agent based on their role
    $retVal['authorized'] =  false;
    if($p_agent_role != null){
        $retVal['authorized'] =  $this->authorize_agent_role_with_ticket($p_agent_role, $base_info['data']['issue_id']);
    }

    $retVal['success'] = 200;
    $retVal['error'] =  [];
    $retVal['data'] =  $base_info;

    return $retVal;
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





// ----------------------------------------------------------
// Provide necessary information based on role & ownership - private functions
// ----------------------------------------------------------
        // -----------------------------------
        // Worker Registration
        // -----------------------------------
        private function get_worker_registration($params){
            $resData = [];
            $resData['data'] = [];
            $resData['error'] = null;
            $resData['status'] = null;
            // Get NBI of ticket
            $nbi = $this->supportTicket->get_nbi_info($params['ticket_ID']);
            // Check for query error
            if($nbi['success'] == false){
                $resData['error'] = "SQLSTATE[42000]: Syntax error or access violation: Please check your query.";
                $resData['status'] = 500;
                // $resData['data'] = $nbi['data'];
                return  $resData;
            }
            // Return full ticket nbi info only if agent is authorized
            if( $params['authorized'] == true ){
                // Store info to return
                $resData['data'] = $nbi["data"];
            } else {
                $limitedNBI_data = [];
                if(count($nbi["data"]) > 0){
                    $limitedNBI_data['worker_name'] = $nbi["data"][0]["worker_name"];
                    $limitedNBI_data['expiration_date'] = $nbi["data"][0]["expiration_date"];
                    $limitedNBI_data['is_verified'] = $nbi["data"][0]["is_verified"];
                    $resData['data'] = Array($limitedNBI_data);  
                } else {
                    $resData['data'] = [];
                }
                // $resData["nbi_info"] = $nbi["data"];
            }

            return  $resData;
        }
        // -----------------------------------
        //  Billing Issue
        // -----------------------------------
        private function get_billing_issue($params){
            $resData = [];
            $resData['data'] = [];
            $resData['error'] = null;
            $resData['status'] = null;
            // Get Billing Details of the ticket
            $bill = $this->supportTicket->get_bill_info($params['ticket_ID']);
            // Check for query error
            if($bill['success'] == false){
                $resData['error'] = "SQLSTATE[42000]: Syntax error or access violation: Please check your query.";
                $resData['status'] = 500;
                // $resData['data'] = $nbi['data'];
                return  $resData;
            }
            // If authorized show billing details
            if( $params['authorized'] == true ){
                $details = $bill['data']['bill_details'];
                if($params['ownership'] != null && $params['ownership'] == true){
                    $resData['data'] = $details[0];
                } else {
                    $limited_details = [];
                    $limited_details["bill_id"] = $details[0]["bill_id"];
                    $limited_details["job_order_id"] = null;
                    $limited_details["job_post_id"] = null;
                    $limited_details["worker_id"] = $details[0]["worker_id"];
                    $limited_details["worker_fname"] = $details[0]["worker_fname"];
                    $limited_details["worker_lname"] = $details[0]["worker_lname"];
                    $limited_details["homeowner_id"] = $details[0]["homeowner_id"];
                    $limited_details["ho_fname"] = $details[0]["ho_fname"];
                    $limited_details["ho_lname"] = $details[0]["ho_lname"];
                    $limited_details["payment_method"] = $details[0]["payment_method"];
                    $limited_details["status"] = $details[0]["status"];
                    $limited_details["status_id"] = $details[0]["bill_status_id"];
                    $limited_details["is_received_by_worker"] = $details[0]["is_received_by_worker"];
                    $limited_details["bill_created_on"] = $details[0]["bill_created_on"];
                    $limited_details["date_time_completion_paid"] = $details[0]["date_time_completion_paid"];
                    $resData['data'] = $limited_details;
                }
            } else {
                $resData['data'] = [];
            }

            return  $resData;
        }
        // -----------------------------------
        // Job Order Issue
        // -----------------------------------
        private function get_job_order_issue($params){
            $resData = [];
            $resData['data'] = [];
            $resData['error'] = null;
            $resData['status'] = null;
            // // Get NBI of ticket
            // $nbi = $this->supportTicket->get_nbi_info($params['ticket_ID']);
            // // Check for query error
            // if($nbi['success'] == false){
            //     $resData['error'] = "SQLSTATE[42000]: Syntax error or access violation: Please check your query.";
            //     $resData['status'] = 500;
            //     // $resData['data'] = $nbi['data'];
            //     return  $resData;
            // }
            // // Return full ticket nbi info only if agent is authorized
            if( $params['authorized'] == true ){
                // Store info to return
                // $resData['data'] = $nbi["data"];
            } else {
                // $limitedNBI_data = [];
                // if(count($nbi["data"]) > 0){
                //     $limitedNBI_data['worker_name'] = $nbi["data"][0]["worker_name"];
                //     $limitedNBI_data['expiration_date'] = $nbi["data"][0]["expiration_date"];
                //     $limitedNBI_data['is_verified'] = $nbi["data"][0]["is_verified"];
                //     $resData['data'] = Array($limitedNBI_data);  
                // } else {
                //     $resData['data'] = [];
                // }
                // // $resData["nbi_info"] = $nbi["data"];
            }

            return  $resData;
        }

        // -----------------------------------
        //  Issue
        // -----------------------------------
        private function trmplate_priv($params){
            $resData = [];
            $resData['data'] = [];
            $resData['error'] = null;
            $resData['status'] = null;

            if( $params['authorized'] == true ){

            } else {

            }
            return  $resData;
        }










}