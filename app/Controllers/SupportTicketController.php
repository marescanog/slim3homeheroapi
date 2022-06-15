<?php

namespace App\Controllers;

use App\Models\File;
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

    protected $file;

    protected $roleThings;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->supportTicket = new SupportTicket();

         $this->supportAgent = new Support();

         $this->validator = new Validator();

         $this->file = new File();

         $this->roleSubTypes = array();
         $this->roleThings = array("Verification Support","Customer Service Support","Technical Support");

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
            if($totalOngoing['success'] == false){
                // return $this->customResponse->is500Response($response,$totalOngoing['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
        // Completed
            $totalCompleted = $this->supportTicket->get_Tickets(3,true,null,$role);
            // Check for query error
            if($totalCompleted['success'] == false){
                // return $this->customResponse->is500Response($response,$totalCompleted['data']);
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
        $resData["completed_total"] = $totalCompleted["data"][0]["COUNT(*)"];
        $resData["new"] = $newTickets["data"];
        $resData["ongoing"] = $ongoingTickets["data"];
        $resData["completed"] = $completedTickets["data"];

        // For debugging
        // $resData["role"] = $role;

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

// ----------------------------------------------------------
// Check if this support ticket has a transfer request
// ----------------------------------------------------------
    $pending_req_obj = $this->supportTicket->check_pending_request($id_ticket);
    // Check for query error
    if($pending_req_obj['success'] == false){
        // return $this->customResponse->is500Response($response,$pending_req_obj['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
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
    $resData['has_pending_tranfer'] = $pending_req_obj = $pending_req_obj['data'] == false ? false : true;


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
            "type"=>v::between(1, 5),
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
        $bill_resolved = CustomRequestHandler::getParam($request,"bill_resolved");
        

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
        $validate_ticket_result = $this->validate_ticket($ticket_id,2,$agent_ID,$role,4);
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
                // Case Cancel Bill
                // $resData["res"] = "Cancel";
                $editRes = $this->supportTicket->process_bill($agent_ID_currrent, $ticket_id, null, 3,  null, $comment);
                if($editRes["success"] == true){
                    if($editRes["data"] == null){
                        $resData['message'] = "Bill not found!";
                    } else {
                        $resData['message'] = "Bill cancelled successfully!";
                        // $resData['data'] = $editRes;
                        $resData['data'] = $editRes['data'];
                    }
                } else {
                    if(isset($editRes["err"])){
                        return $this->return_server_response($response,$editRes["data"],400);
                    } else {
                        // return $this->return_server_response($response,$editRes,500);
                        return $this->return_server_response($response,"Something went wrong when cancelling the bill. Please contact administrator to check SQL syntax.",500);
                    }
                }
                break;
            case 3:
                // Case Notify
                // $resData["res"] = "Notify";
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
            case 4:
                // Case Notify
                // $resData["message"] = "Comment";
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
            case 5:
                // Case Notify
                // $resData["message"] = "Close Ticket";
                if($comment == null){
                    return $this->return_server_response($response,"No comment provided. Please include a comment.",400);
                }  else if( $bill_resolved == null || ($bill_resolved != 1 && $bill_resolved != 2)){
                    return $this->return_server_response($response,"Please indicate if the support ticket has been resolved.",400);
                } else {
                    $closeBillTicketRes = $this->supportTicket->close_ticket($agent_ID_currrent, $ticket_id, $bill_resolved, $comment);
                    if($closeBillTicketRes["success"] == true){
                        $resData['message'] = "The ticket was sucesfully closed.";
                    } else {
                        return $this->return_server_response($response,"An Error occured when updating the ticket. Please try again.",500);
                    }
                }
                break;
            default:
                return $this->return_server_response($response,"Something went wrong while processing your request. Please try again later.",400);
            break;
        }

        

    return $this->return_server_response($response,"This route works",200, $resData);  
}

    

// ===================================================================
//  May 17, 2022

// get the homeowner's address as an authorized representative
public function getAddressList(Request $request,Response $response, array $args)
{
// -----------------------------------
// Get Necessary variables and params
// -----------------------------------
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
    $homeowner_id = $args['id'];

    // Check homeonwer id parameter if valid
    if(!isset($homeowner_id) || $homeowner_id == null || !is_numeric($homeowner_id)){
        return $this->return_server_response($response,"Invalid homeowner ID given.",400);
    }

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

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");

// -----------------------------------
// Auth Agent Information
// -----------------------------------
    $auth_agent_result = $this->authenticate_agent($bearer_token, $agent_ID);
    if($auth_agent_result['success'] != 200){
        return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
    }

// -----------------------------------
// Validate Role
// -----------------------------------
    // Only Customer Service, Supervisor, Admin & Super Admin can get homeowner's address list
    if($role != 2 && $role != 4 && $role != 5 && $role != 6){
        return $this->return_server_response($response,"You are unauthorized to access account holder's address list.",401);
    }

// -----------------------------------
// Get Addresses
// -----------------------------------
    // GET USER ALL ADDRESS
    $allAddress = $this->file->getUsersSavedAddresses($homeowner_id);
    // Error handling
    if(  $allAddress['success'] !== true){
        return $this->customResponse->is500Response($response, $this->generateServerResponse(500,   $allAddress['data']) );
    }

    $resData=[];
    $resData['address_list']=$allAddress['data'];
    // $resData="This route works.";
    return $this->return_server_response($response,"This route works",200,$resData);  
}




// process the job order issue according to the action sent
public function processJobIssue(Request $request,Response $response, array $args)
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
            "type"=>v::between(1, 5),
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
        // Additional info for job order issue edit
        $job_order_status = CustomRequestHandler::getParam($request,"job_order_status");
        $jo_start_date_time = CustomRequestHandler::getParam($request,"jo_start_date_time");
        $jo_end_date_time = CustomRequestHandler::getParam($request,"jo_end_date_time");
        $jo_address_submit = CustomRequestHandler::getParam($request,"home_ID");
        $bill_resolved = CustomRequestHandler::getParam($request,"bill_resolved");

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
        $validate_ticket_result = $this->validate_ticket($ticket_id,2,$agent_ID,$role,7);
        if($validate_ticket_result['success'] != 200){
            return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
        }
        $base_info = $validate_ticket_result['data'];
        $is_owner = $validate_ticket_result['is_owner'];
        $authorized = $validate_ticket_result['authorized'];
        // $ticket_id =  $base_info['data']['id'];
        $agent_ID_currrent =  $base_info['data']['assigned_agent'];

        $resData = [];
// -----------------------------------
// Process Ticket
// -----------------------------------
        switch($type){
            case 1:
                // Case Edit Job Order
                // $resData["res"] = "Edit";

                // Validation
                if(($job_order_status == null || $job_order_status == "") && 
                ($jo_start_date_time == null || $jo_start_date_time == "") &&
                ($jo_end_date_time == null || $jo_end_date_time == "") &&
                ( $jo_address_submit == null ||  $jo_address_submit == "")
                ){
                    return $this->return_server_response($response,"Invalid Entry: All fields blank. Please enter either a new ticket status, job order start time, job order end time or job order address for the edit.",400);
                }

                // GET THE JOB ORDER INFORMATION
                $jo_info_res = $this->supportTicket->get_joborder_from_support_ticket_LIGHT($ticket_id);
                if($jo_info_res["success"] == false){
                    return $this->return_server_response($response,"Something went wrong when updating the Job Order information. Please contact administrator to check SQL syntax.",500);
                }
                $jo_info = $jo_info_res["data"];
                $jo_ID = $jo_info["job_order_id"];
                $jo_current_status = $jo_info["job_order_status_id"];
                $resData['jo_info_res'] =   $jo_info;
                // $resData['same_value'] =   $jo_info["job_order_id"]==$job_order_status;
                
                // Second validation - check if same values with the ones in DB, if it is, then mark as null
                $jo_start_date_time = $jo_info["job_start"]==$jo_start_date_time ? null :  $jo_start_date_time;
                $jo_end_date_time = $jo_info["job_end"]==$jo_end_date_time ? null : $jo_end_date_time;
                $jo_address_submit = $jo_info["home_id"]==$jo_address_submit ? null :  $jo_address_submit;
                $job_order_status = $jo_info["job_order_status_id"]==$job_order_status ? null : $job_order_status;
                $comment = $comment == "" ? null : $comment;
                $previouslyCancelled = $jo_current_status==3;

                $job_post_ID = $jo_info["job_post_id"];

                // $resData['jo_start_date_time'] = $jo_start_date_time;
                // $resData['jo_end_date_time'] = $jo_start_end_time;
        
                $editRes = $this->supportTicket->edit_job_order_issue(
                    $agent_ID_currrent, $ticket_id, $jo_ID, $job_post_ID, 
                    $job_order_status,      // Job Order Status
                    $jo_start_date_time,    // Job Start Time
                    $jo_end_date_time,      // Job End Time
                    $jo_address_submit,     // Job Address Submit
                    $comment, 
                    $previouslyCancelled,
                    1                       // type
                );
                if($editRes["success"] == true){
                    if($editRes["data"] == null || $editRes["data"] == false){
                        $resData['message'] = "There was an error processing the Job Order update!";
                    } else {
                        $resData['message'] = "Job Order updated successfully!";
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
                // Case Cancel Job Order
                // $resData["res"] = "Cancel";

                // GET THE JOB ORDER INFORMATION
                $jo_info_res = $this->supportTicket->get_joborder_from_support_ticket_LIGHT($ticket_id);
                if($jo_info_res["success"] == false){
                    return $this->return_server_response($response,"Something went wrong when updating the Job Order information. Please contact administrator to check SQL syntax.",500);
                }
                $jo_info = $jo_info_res["data"];
                $jo_ID = $jo_info["job_order_id"];
                $jo_current_status = $jo_info["job_order_status_id"];
                $resData['jo_info_res'] =  $jo_info;
                $job_post_ID = $jo_info["job_post_id"];
                $previouslyCancelled = $jo_current_status==3;

                $editRes = $this->supportTicket->edit_job_order_issue(
                    $agent_ID_currrent, $ticket_id, $jo_ID, $job_post_ID, 
                    3,      // Job Order Status
                    null,    // Job Start Time
                    null,      // Job End Time
                    null,     // Job Address Submit
                    $comment, 
                    $previouslyCancelled,
                    2
                );
       
                if($editRes["success"] == true){
                    if($editRes["data"] == null || $editRes["data"] == false){
                        $resData['message'] = "There was an error cancelling the Job Order!";
                    } else {
                        $resData['message'] = "Job Order cancelled successfully!";
                        // $resData['data'] = $editRes;
                        $resData['data'] = $editRes['data'];
                    }
                } else {
                    if(isset($editRes["err"])){
                        return $this->return_server_response($response,$editRes["data"],400);
                    } else {
                        // return $this->return_server_response($response,$editRes,500);
                        return $this->return_server_response($response,"Something went wrong when cancelling the bill. Please contact administrator to check SQL syntax.",500);
                    }
                }

                break;
            case 3:
                // Case Notify
                // $resData["res"] = "Notify";
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
            case 4:
                // Case Notify
                // $resData["message"] = "Comment";
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
            case 5:
                // Case Notify
                // $resData["message"] = "Close Ticket";
                if($comment == null){
                    return $this->return_server_response($response,"No comment provided. Please include a comment.",400);
                }  else if( $bill_resolved == null || ($bill_resolved != 1 && $bill_resolved != 2)){
                    return $this->return_server_response($response,"Please indicate if the support ticket has been resolved.",400);
                } else {
                    $closeBillTicketRes = $this->supportTicket->close_ticket($agent_ID_currrent, $ticket_id, $bill_resolved, $comment);
                    if($closeBillTicketRes["success"] == true){
                        $resData['message'] = "The ticket was sucesfully closed.";
                    } else {
                        return $this->return_server_response($response,"An Error occured when updating the ticket. Please try again.",500);
                    }
                }
                break;
            default:
                return $this->return_server_response($response,"Something went wrong while processing your request. Please try again later.",400);
            break;
        }

    return $this->return_server_response($response,"This route works",200,$resData); 
}








// -----------------------------------
// May 20. 2022
// -----------------------------------
// -----------------------------------
// -----------------------------------
// process the job order issue according to the action sent
public function requestTransfer(Request $request,Response $response, array $args)
{
// -----------------------------------
// Get Necessary variables and params
// -----------------------------------
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
    $ticket_id = $args['ticketID'];

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty(),
        "comments"=>v::notEmpty(),
        "transfer_code"=>v::notEmpty(),
        "transfer_reason"=>v::notEmpty(),
        "sup_id"=>v::notEmpty(),
        "permission_code"=>v::notEmpty()
    ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        $this->validator->validate($request,[
            // Check if empty
            "transfer_reason"=>v::between(1, 5)
        ]);
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $comment = CustomRequestHandler::getParam($request,"comments");
        // Additional info for transfer request
        $sup_id = CustomRequestHandler::getParam($request,"sup_id");
        $transfer_code = CustomRequestHandler::getParam($request,"transfer_code");
        $transfer_reason = CustomRequestHandler::getParam($request,"transfer_reason");
        $permis_code = CustomRequestHandler::getParam($request,"permission_code");

// -----------------------------------
// Get REQUEST SENDERS Information
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
        $supID = $account['data']['supervisor_id'];

// -----------------------------------
// Auth SENDERS Information
// -----------------------------------
        $auth_agent_result = $this->authenticate_agent($bearer_token, $agent_ID);
        if($auth_agent_result['success'] != 200){
            return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
        }

// -----------------------------------
// Validate Ticket
// -----------------------------------
        $validate_ticket_result = $this->validate_ticket($ticket_id,2,$agent_ID,$role,null);
        if($validate_ticket_result['success'] != 200){
            return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
        }
        $base_info = $validate_ticket_result['data'];
        $is_owner = $validate_ticket_result['is_owner'];
        $authorized = $validate_ticket_result['authorized'];
        // $ticket_id =  $base_info['data']['id'];
        $agent_ID_currrent =  $base_info['data']['assigned_agent'];

// -----------------------------------
// SEND TRANSFER REQUEST FOR TICKET
// -----------------------------------
        // Check if Sup ID is same sup or different sup
        if($supID == $sup_id){
            // Validate Override Code 
            $validateRes = $this->validate_override_code($transfer_code, $supID, $permis_code, $ticket_id);
            if($validateRes['success'] != 200){
                return $this->return_server_response($response,$validateRes['error'],$validateRes['success']);
            }
            // Submit a Request Notification
            $sendTransferNotif = $this->supportTicket->sendNotif(
                $sup_id,         // agent's supervisor ID
                $ticket_id,      // support ticket ID
                2,              // Notification Type ID
                $transfer_reason = null, // Transfer Reason ID
                $agent_ID,      // Request Sender's ID (user)
                $permis_code,  // Permissions ID
                $comment        // comment
            );
            if($sendTransferNotif['success'] == false){
                // return $this->customResponse->is500Response($response,$sendTransferNotif['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
        } else {
            // Check if the id is support. If found, check if the role is sup/manager/admin
        }

    $resData = [];
    $resData['sendTransReqResult'] =  $sendTransferNotif['data'];
    // $resData['params'] =  $sendTransferNotif['params'];
    // $resData['validation'] =  $validateRes;
    // $resData['agent_acc'] = $account['data'];
    return $this->return_server_response($response,"This route works",200,$resData); 
}


// +++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++
// Get Notification according to the type of user
public function getNotifications(Request $request,Response $response, array $args)
{
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty(),
        // "view"=>v::notEmpty()
    ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $limit = CustomRequestHandler::getParam($request,"limit");
    $page = CustomRequestHandler::getParam($request,"page");

    // Clean Variables
    $limit =  is_numeric($limit) ? ($limit > 1000 ? 10000 : $limit) : 10;
    $page = (is_numeric($page) ? $page : 1);
    $offset = (($page-1)*$limit); // $page, $limit

    // -----------------------------------
    // Get REQUEST SENDERS Information
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
        $supID = $account['data']['supervisor_id'];

    // -----------------------------------
    // Auth SENDERS Information
    // -----------------------------------
        $auth_agent_result = $this->authenticate_agent($bearer_token, $agent_ID);
        if($auth_agent_result['success'] != 200){
            return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
        }
    
        $limit = $limit != null && is_numeric($limit) ? $limit : 10;
    //     $offset =   $offset != null && is_numeric($offset) ? $offset : 0;

    
        // GET NOTIFICATIONS BASED IF READ OR DELETE
        $notifResNew = $this->supportTicket->get_notifications($agent_ID, null, null, "new", $limit, $offset);
        // Check for query error
        if($notifResNew['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResNew['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        $notifResRead = $this->supportTicket->get_notifications($agent_ID, null, null, "read", $limit, $offset);
        if($notifResRead['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResRead['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        $notifResDone = $this->supportTicket->get_notifications($agent_ID, null, null, "done", $limit, $offset);
        if($notifResRead['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResDone['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        $notifResAll = $this->supportTicket->get_notifications($agent_ID, null, null, "all", $limit, $offset);
        if($notifResAll['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResAll['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }


        // GET TOTALS OF EACH GROUP
        $notifResNewTot = $this->supportTicket->get_notifications($agent_ID, null, null, "new", null, null);
        // $notifResNewTot = $this->supportTicket->get_notifications($agent_ID, null, null, false, false,false, null, null);
        // Check for query error
        if($notifResNewTot['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResNewTot['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        // $notifResReadTot = $this->supportTicket->get_notifications($agent_ID, null, null, true, false,false, null, null);
        $notifResReadTot = $this->supportTicket->get_notifications($agent_ID, null, null, "read", null, null);
        if( $notifResReadTot['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResReadTot['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        $notifResDoneTot = $this->supportTicket->get_notifications($agent_ID, null, null, "done", null, null);
        // $notifResDoneTot = $this->supportTicket->get_notifications($agent_ID, null, null, false, false, true, null, null);
        if( $notifResReadTot['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResDoneTot['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }

        $notifResAllTot = $this->supportTicket->get_notifications($agent_ID, null, null, "all", null, null);
        // $notifResAllTot = $this->supportTicket->get_notifications($agent_ID, null, null, false, true,false, null, null);
        if($notifResAllTot['success'] == false){
            // return $this->customResponse->is500Response($response,$notifResAllTot['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }


    // // Get agent notifications ?
    // //  Check if it is the same I think it is


    $resData = [];
    $resData['new'] = $notifResNew['data'];
    $resData['read'] = $notifResRead['data'];
    $resData['all'] = $notifResAll['data'];
    $resData['done'] = $notifResDone['data'];
    $resData['new_total'] = $notifResNewTot['data'][0]['total'];
    $resData['read_total'] = $notifResReadTot['data'][0]['total'];
    $resData['all_total'] =  $notifResAllTot['data'][0]['total'];
    $resData['done_total'] = $notifResDoneTot['data'][0]['total'];

    return $this->return_server_response($response,"This route works",200,$resData); 
}



// may 21, 2022
// +++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++
// Agents applicable for transfer are this sup's agents plus the agents of the person who sent the notification
public function getAgentsApplicableForTransfer(Request $request,Response $response, array $args)
{
// -----------------------------------
// Get Necessary variables and params
// -----------------------------------
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
    $notif_id = $args['notifID'];

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
    // Clean Data
    if(!is_numeric($notif_id)){
        return $this->customResponse->is400Response($response,"Something went wrong with pulling up the data. Please try again!");
    }

// -----------------------------------
// Get this processor Information
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
        $processors_ID = $account['data']['id'];
        $processors_role = $account['data']['role_type'];
        $processors_supID = $account['data']['supervisor_id'];
        // Check if the processor has the correct role (4 and above)
        if($processors_role < 4){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "Unauthorized Access: Please login to an authorized account to process this request."));
        }

// -----------------------------------
// Auth this processor_agents (sup) Information
// -----------------------------------
        $auth_processor_result = $this->authenticate_agent($bearer_token, $processors_ID);
        if($auth_processor_result['success'] != 200){
            return $this->return_server_response($response,$auth_processor_result['error'],$auth_processor_result['success']);
        }

    // Get Notification with ID
    $notifObjRes = $this->supportTicket->getNotificationUsingNotificationID($notif_id);
    // Check for query error
    if($notifObjRes['success'] == false){
        // return $this->customResponse->is500Response($response,$notifObjRes['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    // Check if notif found
    if($notifObjRes['data'] == false){
        return $this->customResponse->is404Response($response,"Notification was not found! Please try again!");
    }
    $request_maker = $notifObjRes['data']['generated_by'];
    $support_ticket_ID = $notifObjRes['data']['support_ticket_id'];


    // Get the request Maker's supervisor & Role Type
    $requestMakersSupervisorAndRoleObject = $this->supportTicket->getTheSupervisorOfAgentUsingAgentID($request_maker);
    // Check for query error
    if($requestMakersSupervisorAndRoleObject['success'] == false){
        // return $this->customResponse->is500Response($response,$requestMakersSupervisorAndRoleObject['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    $request_makers_supervisor = $requestMakersSupervisorAndRoleObject['data'] == false ? null 
                : $requestMakersSupervisorAndRoleObject['data']['supervisor_id'];
    $isThisMyAgent = $request_makers_supervisor  == null 
                    ? false 
                    : $request_makers_supervisor  == $processors_ID; // (processors id is my id)


    // Note: Request maker's role is not a good determination for which role to base the agent search on since roles can chance
    //          Base it on the support ticket instead thus pull support ticket details
    // Get the support ticket in question
    $ticketBaseInfoLightObj = $this->supportTicket->get_ticket_base_info_LIGHT($support_ticket_ID);
    // Check for query error
    if($ticketBaseInfoLightObj['success'] == false){
        // return $this->customResponse->is500Response($response,$ticketBaseInfoLightObj['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    // Check if support ticket found
    if($ticketBaseInfoLightObj['data'] == false){
        return $this->customResponse->is404Response($response,"Something went wrong with the request! Please try again!");
    }
    $ticketStatus = $ticketBaseInfoLightObj['data']['status'];
    $ticketisArchived = $ticketBaseInfoLightObj['data']['is_Archived']; // Not gonna use this for now but putting it here just in case
    $ticketIssueID = $ticketBaseInfoLightObj['data']['issue_id'];
    $ticketAgentID = $ticketBaseInfoLightObj['data']['assigned_agent'];
    $role_needed = $this->roleSubTypes[$ticketIssueID]; // Gets the role needed based on the issue ID of the ticket
    // Get the role needed based off of the issue ID
// -----------------------------------
// Validate Ticket
// -----------------------------------
    // Not using built in validator since were just checking if the status is still applicable
    if($ticketStatus != 2 && $ticketStatus != 1){
        // This means ticket is closed/resolved. Thus a transfer is not needed so just delete the request


        // ADD DELETE CODE HERE LATER

    }

    if( $ticketAgentID != $request_maker){
        // This means ticket's original agent is already different, thus the ticket has already been transferred
        // Check to see if the notif has already been processed and if not processed, delete the notif
        // This is only applicable for transfer requests and not override requests, so check which request it is first
        // Check the notification notification type id (should indicate if ovveride or not)

        // ADD CHECK & DELETE CODE HERE LATER
    }



// if everything is good proceed with getting the applicable agents
$is_the_same_supervisor = $request_makers_supervisor == null ? false : ($request_makers_supervisor == $processors_ID); // Compare $request_makers_supervisor $processors_ID 
$allApplicableAgents = [];
$myAgentsArr = [];
$request_makers_team_mates = [];
$resData = [];

    // -------------------------------------------------------------------
    // Get All My Agents -> Applicable with role
    $myAgentsObj = $this->supportTicket->getAgentsOfSupervisor(
        $processors_ID,    // Supervisors ID
        $role_needed,    // Optional role filter -> we want the same role as the ticket in question
        2,     // Optional status filter -> we want all active agents
        false, // Optional email included in data return
        false // Optional mobile number included in data return
    );
    // Check for query error
    if($myAgentsObj['success'] == false){
        // return $this->customResponse->is500Response($response,$myAgentsObj['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    $myAgentsArr =  $myAgentsObj['data']; 


// No need to pull up data if the same supervisor, only pull when different supervisor
if(!$is_the_same_supervisor){

    // -------------------------------------------------------------------
    // Get All request_makers team mates -> Applicable with role
    $request_makers_team_mates_OBJ = $this->supportTicket->getAgentsOfSupervisor(
        $request_makers_supervisor,    // Supervisors ID
        $role_needed,    // Optional role filter -> we want the same role as the ticket in question
        2,     // Optional status filter -> we want all active agents
        false, // Optional email included in data return
        false // Optional mobile number included in data return
    );
    // Check for query error
    if($request_makers_team_mates_OBJ['success'] == false){
        // return $this->customResponse->is500Response($response,$request_makers_team_mates_OBJ['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }
    $request_makers_team_mates =  $request_makers_team_mates_OBJ['data'];
}



// // For reference
// $allApplicableAgents = [];
// $myAgentsArr = [];
// $request_makers_team_mates = [];
$continue = true;

    // Both are blank
    if(count($myAgentsArr) == 0 && count($request_makers_team_mates) == 0){
        // // Get all applicable agents under the role
    // Note: Wrong logic, send a blank array since if there are no agents detected under the supervisor or team mates
    // then the supervisor has to manually enter an agents ID [Keeping this code here for reference]

        // $allApplicableAgentsUnderTheRoleObj = $this->supportTicket->getAllAgentsUnderARole(
        //     $role_needed,                  // role
        //     2,           // status
        //     false,       // include agents email in data return
        //     false       // include agents phone number in data return
        // );
        // if($allApplicableAgentsUnderTheRoleObj['success'] == false){
        //     // return $this->customResponse->is500Response($response,$myAgentsObj['data']);
        //     return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        // }
        // // $allApplicableAgents =  $allApplicableAgentsUnderTheRoleObj['data']; 
        // // copy array to allApplicableAgents minus $request_maker who is also  $ticketAgentID // to get ID $allApplicableAgents[0]['id']; 
        // for($kapoy = 0 ; $kapoy < count($allApplicableAgentsUnderTheRoleObj); $kapoy++){
        //     if($allApplicableAgentsUnderTheRoleObj[$kapoy]['id'] != $ticketAgentID){ 
        //         array_push($allApplicableAgents, $allApplicableAgentsUnderTheRoleObj['data'][$kapoy]);
        //     }  
        // }

        $continue = false;
    }

    // same sup - no need to combine
    if($continue == true && $is_the_same_supervisor){
        // $allApplicableAgents = array_merge(array(), $myAgentsArr);
        // copy array to allApplicableAgents minus $request_maker who is also  $ticketAgentID
        for($kapoy = 0 ; $kapoy < count($myAgentsArr); $kapoy++){
            if($myAgentsArr[$kapoy]['id'] != $ticketAgentID){ 
                array_push($allApplicableAgents, $myAgentsArr[$kapoy]);
            }  
        }
        $continue = false;
    }

    // Different Sup Then Combine Array
    if($continue == true && !$is_the_same_supervisor){
        // $allApplicableAgents = array_merge($myAgentsArr, $request_makers_team_mates);
        // copy array to allApplicableAgents minus $request_maker who is also  $ticketAgentID
        for($kapoy = 0 ; $kapoy < count($myAgentsArr); $kapoy++){
            if($myAgentsArr[$kapoy]['id'] != $ticketAgentID){ 
                array_push($allApplicableAgents, $myAgentsArr[$kapoy]);
            }  
        }
        for($kapoy = 0 ; $kapoy < count($request_makers_team_mates); $kapoy++){
            if($request_makers_team_mates[$kapoy]['id'] != $ticketAgentID){ 
                array_push($allApplicableAgents, $request_makers_team_mates[$kapoy]);
            }  
        }
    }
 

    // For debugging purposes 
        // $resData['my_ID'] =  $processors_ID ; 
        $resData['agents_list'] =  $allApplicableAgents; 
        // $resData['request_maker'] =  $request_maker;
        // $resData['requestMaker_isSameWith_supportTicketAgent'] =  $ticketAgentID == $request_maker;
        // $resData['request_makersTeamMates'] =  $request_makers_team_mates; 
        // $resData['request_makers_supervisor'] =  $request_makers_supervisor; $request_maker
        // $resData['my_ID'] =  $processors_ID ; 
        // $resData['supervisorsagents'] =  $myAgentsObj['data']; 
        // $resData['role_needed'] = $role_needed; 
        // $resData['supportTicketInfo'] =  $ticketBaseInfoLightObj['data'];
        // $resData['notifObjRes'] =  $notifObjRes['data'];
        // $resData['requestMaker'] = $request_maker;
        // $resData['requestMakersSupervisorAndRole'] = $requestMakersSupervisorAndRoleObject['data'];
        // $resData['hasSuperVisor'] = $hasSupervisor;
        // $resData['isThisMyAgent'] = $isThisMyAgent;

    return $this->return_server_response($response,"This route works",200,$resData); 
}



// =======================================================================================
// =======================================================================================
// =======================================================================================
// PROCESS THE DAMN TRANSFER
public function processTransfer(Request $request,Response $response, array $args)
{
    $resData = [];
// -----------------------------------
// Get Necessary variables and params
// -----------------------------------
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
    $notif_ID = $args['notifID'];

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty(),
        "transfer_to_agent_id"=>v::notEmpty()
    ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $transfer_to_agent_id = CustomRequestHandler::getParam($request,"transfer_to_agent_id");

// -----------------------------------
// Get Ticket Processer's Information (Request Sender)
// -----------------------------------
        // Get processor's ID with email
        $account = $this->supportAgent->getSupportAccount($email);
        // Check for query error
        if($account['success'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Check if email is valid by seeing if account is found
        if($account['data'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token & email not found. Please sign into your account."));
        }
// Variables Group 1
// Get processor's account, role, & sup ID
    $processor_ID = $account['data']['id'];
    $processor_role = $account['data']['role_type'];
    $processor_supID = $account['data']['supervisor_id'];

// -----------------------------------
// Validation 1 - Check role of processor (request sender)
        // If the request sender is not a supervisor, manager or admin, deny request
        if($processor_role < 4){
             // return $this->customResponse->is500Response($response,$account['data']);
             return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "Unauthorized Access: Please sign into an authorized account to process this request."));
        }

// -----------------------------------
// Auth SENDERS Information (JWT AUTH)
// -----------------------------------
        $auth_agent_result = $this->authenticate_agent($bearer_token, $processor_ID);
        if($auth_agent_result['success'] != 200){
            return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
        }

// -----------------------------------
// Get Notification Details using the notification ID
// -----------------------------------
        $notificationObj = $this->supportTicket->getNotificationUsingNotificationID($notif_ID);
        // Check for query error
        if($notificationObj['success'] == false){
            // return $this->customResponse->is500Response($response,$notificationObj['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        } 
        // Check if data is found
        if($notificationObj['data'] == false){
            // return $this->customResponse->is500Response($response,$notificationObj['data']);
            return $this->customResponse->is404Response($response,$this->generateServerResponse(404, "An error occured while processing your request. The notification cannot be found. Please resfresh the browser and try again."));
        }
// Variables Group 2
// Get Support Ticket ID, Ticket Actions ID, notifications Type, 
// permissions ID, permissions owner, is read
$notification_data = $notificationObj['data'];
$notif_creator_ID = $notification_data != null ? $notification_data['generated_by'] : null; // should be the same as the assigned agent in the ticket (if transfer request, but if override request should be different)
$suport_ticket_ID = $notification_data != null ? $notification_data['support_ticket_id'] : null;
$ticket_actions_ID = $notification_data != null ? $notification_data['ticket_actions_id'] : null;
$notification_type_ID = $notification_data != null ? $notification_data['notification_type_id'] : null;
$permissions_ID = $notification_data != null ? $notification_data['permissions_id'] : null;
$permissions_owner_ID = $notification_data != null ? $notification_data['permissions_owner'] : null;
$has_taken_action_on_notification = $notification_data != null ? $notification_data['has_taken_action'] : null;
$has_notification_been_deleted = $notification_data != null ? $notification_data['is_deleted'] : null;
$notification_sysgen = $notification_data != null ? $notification_data['system_generated_description'] : null;
$notif_recipient_ID = $notification_data != null ? $notification_data['recipient_id'] : null;
// // For Debugging purposes
// //  $resData["notificationObj"] = $notificationObj['data'];
// $resData["notification_data"] = $notification_data;
// $resData["suport_ticket_ID"] = $suport_ticket_ID;
// $resData["ticket_actions_ID"] = $ticket_actions_ID;
// $resData["notification_type_ID"] = $notification_type_ID;
// $resData["permissions_ID"] = $permissions_ID;
// $resData["permissions_owner_ID"] = $permissions_owner_ID;
// $resData["has_taken_action_on_notification"] = $has_taken_action_on_notification;
// $resData["has_notification_been_deleted"] = $has_notification_been_deleted;
// $resData["notification_sysgen"] = $notification_sysgen;

// GET TRANSFER REASON FROM SYSGEN
$transfer_reason_ID = null;
$sysgen_end_reason = explode(" REASON-R", $notification_sysgen);
if(count($sysgen_end_reason) != 0){
    $transfer_reason_ID_arr = explode(" ", $sysgen_end_reason[1]);
    if(count($transfer_reason_ID_arr) != 0){
        $transfer_reason_ID = $transfer_reason_ID_arr[0];
    }
}
// $resData["transfer_ID"] = $transfer_reason_ID;
//  Note permissions_ID & permissions_owner_ID can be used to pull the override code data

// -----------------------------------
// Validate Ticket
// -----------------------------------
        // Does several checks, valid ID, valid status, if found etc.
        $validate_ticket_result = $this->validate_ticket($suport_ticket_ID,2,$processor_ID,$processor_role,null);
        if($validate_ticket_result['success'] != 200){
            return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
        }
// Variables Group 3
// Get Support Ticket base info, support ticket agent, support ticket role needed
        $support_ticket_base_info = $validate_ticket_result['data']['data'];
        $is_processor_the_ticket_owner = $validate_ticket_result['is_owner'];
        $is_processor_authorized = $validate_ticket_result['authorized']; // authorized to work on ticket
        $support_ticket_agent =  $support_ticket_base_info['assigned_agent'];
        $support_ticket_issue_ID = $support_ticket_base_info['issue_id'];
        $role_needed = $this->roleSubTypes[$support_ticket_issue_ID];
// // For Debugging purposes
// // $resData["validate_ticket_result"] = $validate_ticket_result;
// $resData["support_ticket_base_info"] = $support_ticket_base_info;
// $resData["is_processor_the_ticket_owner"] = $is_processor_the_ticket_owner;
// $resData["is_processor_authorized"] = $is_processor_authorized;
// $resData["support_ticket_agent"] = $support_ticket_agent; // should be same as notification creator (if transfer request, but if override request should be different)
// $resData["support_ticket_issue_ID"] = $support_ticket_issue_ID;
// $resData["role_needed"] = $role_needed;


// ----------------------------------------
// Get the information of the chosen agent
// ----------------------------------------
        $chosen_agent_info = $this->supportTicket->getTheSupervisorOfAgentUsingAgentID($transfer_to_agent_id);
        // Check for query error
        if($chosen_agent_info['success'] == false){
            // return $this->customResponse->is500Response($response,$chosen_agent_info['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        } 
        // Check if data is found
        if($chosen_agent_info['data'] == false){
            // return $this->customResponse->is500Response($response,$chosen_agent_info['data']);
            return $this->customResponse->is404Response($response,$this->generateServerResponse(404, "An error occured while processing your request. Employee ID entered is invalid and does not match any records. Please enter a valid employee ID."));
        }
// Variables Group 4
// Get chosen agent role, chosen agent supervisor, chosen agent status, chosen agent is deleted
        $chosen_agent_data = $chosen_agent_info['data'];
        $chosen_agent_role = $chosen_agent_data['role_type'];
        $chosen_agent_sup = $chosen_agent_data['supervisor_id'];
        $chosen_agent_status = $chosen_agent_data['active_status'];
        $chosen_agent_is_deleted = $chosen_agent_data['is_deleted'];
// // For Debugging purposes
// // $resData["chosen_agent_info"] = $chosen_agent_info['data'];
// $resData["chosen_agent_data"] = $chosen_agent_data['is_deleted'];
// $resData["chosen_agent_role"] = $chosen_agent_role;
// $resData["chosen_agent_supervisor_id"] = $chosen_agent_sup;
// $resData["chosen_agent_status"] = $chosen_agent_status;
// $resData["chosen_agent_is_deleted"] = $chosen_agent_is_deleted;
// ------------------------------------------------------------------
        // Validate if the chosen agent is still active and not deleted
        if($chosen_agent_status != 2){
            // $resData["chosen_agent_status"] = $chosen_agent_status;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, "Bad Request: The account of the chosen employee is not yet active or has been disabled. Please select a supervisor or a ".($this->roleThings[$role_needed-1])." to transfer this ticket to."));
        }
        if($chosen_agent_is_deleted >= 1){
            // $resData["chosen_agent_is_deleted"] = $chosen_agent_is_deleted;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(404, "Bad Request: The chosen employee is no longer active. Please select a supervisor or a ".($this->roleThings[$role_needed-1])." to transfer this ticket to."));
        }
        // Validate if the chosen agent is a manager or admin (They cannot accept tickets)
        if($chosen_agent_role > 4){
            // $resData["chosen_agent_role"] = $chosen_agent_role;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(404, "Bad Request: Transfers are not permitted to Managers or Admin staff. Please select a supervisor or a ".($this->roleThings[$role_needed-1])." to transfer this ticket to."));
        }
        $is_chosen_agent_a_supervisor  =  $chosen_agent_role == 4 ? 2 : 1;

// ------------------------------------------------------------------
// Get the information of the agent who is also assigned in ticket
// ------------------------------------------------------------------
        $from_agent_info = $this->supportTicket->getTheSupervisorOfAgentUsingAgentID($notif_creator_ID);
        // Check for query error
        if($from_agent_info['success'] == false){
            // return $this->customResponse->is500Response($response,$chosen_agent_info['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        } 
        // Check if data is found
        if($from_agent_info['data'] == false){
            // return $this->customResponse->is500Response($response,$chosen_agent_info['data']);
            return $this->customResponse->is404Response($response,$this->generateServerResponse(404, "An error occured while processing your request. Please resfresh the browser and try again."));
        }
// Variables Group 5
// Get from agent role, from agent supervisor, from agent status, from agent is deleted
        $from_agent_data = $from_agent_info['data'];
        $from_agent_role = $from_agent_data['role_type'];
        $from_agent_sup = $from_agent_data['supervisor_id'];
        $from_agent_status = $from_agent_data['active_status'];
        $from_agent_is_deleted = $from_agent_data['is_deleted'];
// // For Debugging purposes
// // $resData["from_agent_info"] = $found_agent_info['data'];
// $resData["from_agent_data"] = $from_agent_is_deleted;
// $resData["from_agent_role"] = $from_agent_role;
// $resData["from_agent_supervisor_id"] = $from_agent_sup;
// $resData["from_agent_status"] = $from_agent_status;
// $resData["from_agent_is_deleted"] = $from_agent_is_deleted;


// ---------------------------------------------------------------
// VALIDATE TRANSFER REQUEST FOR TICKET BASED ON VARIABLES GIVEN
// ---------------------------------------------------------------
// Supervisor can reassign the ticket to a different agent on the Requester's team
// Or on the same team the supervisor is handling
// is this the same notification recipient ?
if($notif_recipient_ID != $processor_ID){
    return $this->customResponse->is401Response($response,"Token Expired! Please log into your account and try again!");
}
// Has the notification already been processed ?
    if($has_taken_action_on_notification != 0){
        return $this->customResponse->is400Response($response,"Bad Request: This notification has already been processed.");
    }
// Has the notification already been deleted ?
    if($has_notification_been_deleted != 0){
        return $this->customResponse->is400Response($response,"Bad Request: This notification has already been deleted.");
    }
// Does the chosen agent have the same role_needed as the issue of the ticket ? (If not a supervisor)
        if(($chosen_agent_role != $role_needed) && ($chosen_agent_role != 4)){
            // $resData["chosen_agent_role"] = $chosen_agent_role;
            // $resData["role_needed"] = $role_needed;
            return $this->customResponse->is400Response($response,$this->generateServerResponse(400, "Bad Request: The chosen agent is outside the scope of the support ticket. Please select an agent with the role of ".($this->roleThings[$role_needed-1])." to transfer this ticket to."));
        }
// Check if chosen agent is the same as the agent who made the request
    if($transfer_to_agent_id == $notif_creator_ID){
        return $this->customResponse->is400Response($response,"Bad Request: Chosen agent is the same agent who made the transfer request. Please select a different agent to transfer the ticket to.");
    }

// Check if the chosen agent is part of supervisor id's team
    $is_in_my_team = $chosen_agent_sup == $processor_ID;

// Check if the chosen agent is a team mate of the agent who created the request
    $is_a_team_mate = $chosen_agent_sup == $from_agent_sup;

// Check if the chosen agent is the one processing the transfer (Sup can transfer the ticket to themselves)
    $is_transfer_to_me = $transfer_to_agent_id == $processor_ID;


// Other Notes support ticket agent the same as notification creator? (if transfer ticket request, but if override ticket request should be different)
//   //  still need to check
//   // No need to ask agent, but ask sup for code -  External Agent Sends a Transfer Req to Sup meaning (permission 1: External Agent Transfer Request) has been granted
//   // No need to ask agent for code - Internal Agent Sends a Transfer Req to Sup meaning (permission 2: Internal Agent Transfer Request) has been granted
//   // ================================== // ask/check for transfer code if external transfer


// // ---------------------------------------------------------------
// //      INTERNAL TRANSFER
// // ---------------------------------------------------------------
    if( $is_in_my_team == true ||  $is_a_team_mate ||  $is_transfer_to_me){
        // If it is within the team, no need to check for approval code from manager
        // Can transfer to self
        // ==============
        // Process transfer
        $resObj_transfer = $this->supportTicket->processTransferRequest(
            $notif_ID,          // notification ID
            $suport_ticket_ID,  // support ticket ID
            $transfer_to_agent_id,  // chosen agent (transfer to)
            $notif_creator_ID,      // from agent (request creator)
            $transfer_reason_ID,     // transfer reason
            $processor_ID,         // processorsID
            $is_chosen_agent_a_supervisor,      // Transfer to supervisor
            false,                    // is it an external transfer
            null,           // the processors manager (sup)
            $from_agent_sup,         // the supervisor of the from agent
            $chosen_agent_sup         // the supervisor of the chosen agent
        );
        // $resData['sql'] = $resObj_transfer['data'];
        // Check for query error
        if(!isset($resObj_transfer) || $resObj_transfer['success'] == null || $resObj_transfer['success'] == false){
            // return $this->customResponse->is500Response($response,$resObj_transfer);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        } 
        $resData['message'] = "Ticket Transfer Successful!";
    }else{
// // ---------------------------------------------------------------
// //      EXTERNAL TRANSFER
// // ---------------------------------------------------------------
        // If it is an external transer, check the code for approval code from manager
        // Can transfer to another supervisor
        // Get Manager approval code
        $this->validator->validate($request,[
            // Check if empty
            "manager_approval_code"=>v::notEmpty()
        ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            if( $chosen_agent_role == 4){
                return $this->customResponse->is400Response($response,"Manager Approval Code is needed to transfer a ticket to a Supervisor. Please enter your manager's approval code or if not other supervisors are available, transfer to yourself or another agent.");
            } else {
                return $this->customResponse->is400Response($response,"Manager Approval Code is needed to transfer a ticket to an agent who is not within your team or the team of the assigned agent. Please enter your manager's approval code.");
            }
        }
        $manager_approval_code = CustomRequestHandler::getParam($request,"manager_approval_code");

        if($manager_approval_code == ""){
            return $this->customResponse->is400Response($response,"Manager Approval Code is needed to transfer a ticket to a Supervisor. Please enter your manager's approval code or if not other supervisors are available, transfer to yourself or another agent.");
        }

        // ================================== Validation of the manager approval code
        // Check if approval code is correct 
            // Validate Override Code 
            $validateRes = $this->validate_override_code($manager_approval_code , $processor_ID, 1, $suport_ticket_ID);
            if($validateRes['success'] != 200){
                return $this->return_server_response($response,$validateRes['error'],$validateRes['success']);
            }
            
        // Process Transfer
        $resObj_transfer = $this->supportTicket->processTransferRequest(
            $notif_ID,          // notification ID
            $suport_ticket_ID,  // support ticket ID
            $transfer_to_agent_id,  // chosen agent (transfer to)
            $notif_creator_ID,      // from agent (request creator)
            $transfer_reason_ID,     // transfer reason
            $processor_ID,         // processorsID
            $is_chosen_agent_a_supervisor,      // Transfer to supervisor
            true,                    // is it an external transfer
            $processor_supID,           // the processors manager (sup)
            $from_agent_sup,         // the supervisor of the from agent
            $chosen_agent_sup         // the supervisor of the chosen agent
        );
        // $resData['sql'] = $resObj_transfer['data'];
        // Check for query error
        if(!isset($resObj_transfer) || $resObj_transfer['success'] == null || $resObj_transfer['success'] == false){
            return $this->customResponse->is500Response($response,$resObj_transfer['data']);
            // return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        } 
        $resData['message'] = "Ticket Transfer Successful!";
    }


    return $this->return_server_response($response,"This route works",200,$resData); 
}





// may 23, 2022
// +++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++
// +++++++++++++++++++++++++++++++++++++++++++
// Agents applicable for transfer are this sup's agents plus the agents of the person who sent the notification
public function declineTransfer(Request $request,Response $response, array $args)
{
    $resData = [];
// -----------------------------------
// Get Necessary variables and params
// -----------------------------------
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // Get ticket parameter for ticket information
    $notif_ID = $args['notifID'];

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty(),
        "comment"=>v::notEmpty(),
        "transfer_type"=>v::notEmpty()
    ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $comment = CustomRequestHandler::getParam($request,"comment");
    $transfer_type = CustomRequestHandler::getParam($request,"transfer_type");

// clean data
if(!is_numeric($transfer_type)){
    $transfer_type = 2;
}

// -----------------------------------
// Get Ticket Processer's Information (Request Sender)
// -----------------------------------
        // Get processor's ID with email
        $account = $this->supportAgent->getSupportAccount($email);
        // Check for query error
        if($account['success'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        }
        // Check if email is valid by seeing if account is found
        if($account['data'] == false){
            // return $this->customResponse->is500Response($response,$account['data']);
            return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token & email not found. Please sign into your account."));
        }
// Variables Group 1
// Get processor's account, role, & sup ID
    $processor_ID = $account['data']['id'];
    $processor_role = $account['data']['role_type'];
    $processor_supID = $account['data']['supervisor_id'];

// -----------------------------------
// Validation 1 - Check role of processor (request sender)
        // If the request sender is not a supervisor, manager or admin, deny request
        if($processor_role < 4){
             // return $this->customResponse->is500Response($response,$account['data']);
             return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "Unauthorized Access: Please sign into an authorized account to process this request."));
        }

// -----------------------------------
// Auth SENDERS Information (JWT AUTH)
// -----------------------------------
        $auth_agent_result = $this->authenticate_agent($bearer_token, $processor_ID);
        if($auth_agent_result['success'] != 200){
            return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
        }

// -----------------------------------
// Get Notification Details using the notification ID
// -----------------------------------
        $notificationObj = $this->supportTicket->getNotificationUsingNotificationID($notif_ID);
        // Check for query error
        if($notificationObj['success'] == false){
            // return $this->customResponse->is500Response($response,$notificationObj['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        } 
        // Check if data is found
        if($notificationObj['data'] == false){
            // return $this->customResponse->is500Response($response,$notificationObj['data']);
            return $this->customResponse->is404Response($response,$this->generateServerResponse(404, "An error occured while processing your request. The notification cannot be found. Please resfresh the browser and try again."));
        }
// Variables Group 2
// Get Support Ticket ID, Ticket Actions ID, notifications Type, 
// permissions ID, permissions owner, is read
$notification_data = $notificationObj['data'];
$notif_creator_ID = $notification_data != null ? $notification_data['generated_by'] : null; // should be the same as the assigned agent in the ticket (if transfer request, but if override request should be different)
$suport_ticket_ID = $notification_data != null ? $notification_data['support_ticket_id'] : null;
$ticket_actions_ID = $notification_data != null ? $notification_data['ticket_actions_id'] : null;
$notification_type_ID = $notification_data != null ? $notification_data['notification_type_id'] : null;
$permissions_ID = $notification_data != null ? $notification_data['permissions_id'] : null;
$permissions_owner_ID = $notification_data != null ? $notification_data['permissions_owner'] : null;
$has_taken_action_on_notification = $notification_data != null ? $notification_data['has_taken_action'] : null;
$has_notification_been_deleted = $notification_data != null ? $notification_data['is_deleted'] : null;
$notification_sysgen = $notification_data != null ? $notification_data['system_generated_description'] : null;
$notif_recipient_ID = $notification_data != null ? $notification_data['recipient_id'] : null;
// // For Debugging purposes
// //  $resData["notificationObj"] = $notificationObj['data'];
// $resData["notification_data"] = $notification_data;
// $resData["suport_ticket_ID"] = $suport_ticket_ID;
// $resData["ticket_actions_ID"] = $ticket_actions_ID;
// $resData["notification_type_ID"] = $notification_type_ID;
// $resData["permissions_ID"] = $permissions_ID;
// $resData["permissions_owner_ID"] = $permissions_owner_ID;
// $resData["has_taken_action_on_notification"] = $has_taken_action_on_notification;
// $resData["has_notification_been_deleted"] = $has_notification_been_deleted;
// $resData["notification_sysgen"] = $notification_sysgen;

// GET TRANSFER REASON FROM SYSGEN
$transfer_reason_ID = null;
$sysgen_end_reason = explode(" REASON-R", $notification_sysgen);
if(count($sysgen_end_reason) != 0){
    $transfer_reason_ID_arr = explode(" ", $sysgen_end_reason[1]);
    if(count($transfer_reason_ID_arr) != 0){
        $transfer_reason_ID = $transfer_reason_ID_arr[0];
    }
}
// $resData["transfer_ID"] = $transfer_reason_ID;
//  Note permissions_ID & permissions_owner_ID can be used to pull the override code data

// -----------------------------------
// Validate Ticket
// -----------------------------------
        // Does several checks, valid ID, valid status, if found etc.
        $validate_ticket_result = $this->validate_ticket($suport_ticket_ID,2,$processor_ID,$processor_role,null);
        if($validate_ticket_result['success'] != 200){
            return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
        }
// Variables Group 3
// Get Support Ticket base info, support ticket agent, support ticket role needed
        $support_ticket_base_info = $validate_ticket_result['data']['data'];
        $is_processor_the_ticket_owner = $validate_ticket_result['is_owner'];
        $is_processor_authorized = $validate_ticket_result['authorized']; // authorized to work on ticket
        $support_ticket_agent =  $support_ticket_base_info['assigned_agent'];
        $support_ticket_issue_ID = $support_ticket_base_info['issue_id'];
        $role_needed = $this->roleSubTypes[$support_ticket_issue_ID];
// // For Debugging purposes
// // $resData["validate_ticket_result"] = $validate_ticket_result;
// $resData["support_ticket_base_info"] = $support_ticket_base_info;
// $resData["is_processor_the_ticket_owner"] = $is_processor_the_ticket_owner;
// $resData["is_processor_authorized"] = $is_processor_authorized;
// $resData["support_ticket_agent"] = $support_ticket_agent; // should be same as notification creator (if transfer request, but if override request should be different)
// $resData["support_ticket_issue_ID"] = $support_ticket_issue_ID;
// $resData["role_needed"] = $role_needed;

// -----------------------------------
// Process Transfer Decline
// -----------------------------------
    // Has the notification already been processed ?
    if($has_taken_action_on_notification != 0){
        return $this->customResponse->is400Response($response,"Bad Request: This notification has already been processed.");
    }
    // Has the notification already been deleted ?
    if($has_notification_been_deleted != 0){
        return $this->customResponse->is400Response($response,"Bad Request: This notification has already been deleted.");
    }
    // is this the same notification recipient ?
    if($notif_recipient_ID != $processor_ID){
        return $this->customResponse->is401Response($response,"Token Expired! Please log into your account and try again!");
    }
    // Proceed
        $resObj_decline = $this->supportTicket->declineTransferRequest(
            $notif_ID,           // notifID
            $suport_ticket_ID,          // supportTicketID
            $comment,                   // $comment
            $processor_ID,                    // supID
            $transfer_type,           // transferID
            $notif_creator_ID
        );
        // Check for query error
        if(!isset( $resObj_decline) ||  $resObj_decline['success'] == null ||  $resObj_decline['success'] == false){
            // return $this->customResponse->is500Response($response, $resObj_decline['data']);
            return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
        } 
        $resData['decline'] = $resObj_decline['data'];
        $resData['message'] = "Succesfully decline request";

    return $this->return_server_response($response,"This route works",200,$resData); 
}



// Agents applicable for transfer are this sup's agents plus the agents of the person who sent the notification
public function toggleReadNotif(Request $request,Response $response, array $args)
{
    $resData = [];
    // // -----------------------------------
    // // Get Necessary variables and params
    // // -----------------------------------
    //     // Get the bearer token from the Auth header
    //     $bearer_token = JSON_encode($request->getHeader("Authorization"));
        // Get ticket parameter for ticket information
        $notif_ID = $args['notifID'];
    
    //     // Get Agent Email for validation
    //     $this->validator->validate($request,[
    //         // Check if empty
    //         "email"=>v::notEmpty()
    //     ]);
    //         // Returns a response when validator detects a rule breach
    //         if($this->validator->failed())
    //         {
    //             $responseMessage = $this->validator->errors;
    //             return $this->customResponse->is400Response($response,$responseMessage);
    //         }
    
    //     // Store Params
    //     $email = CustomRequestHandler::getParam($request,"email");
    
    // // -----------------------------------
    // // Get Ticket Processer's Information (Request Sender)
    // // -----------------------------------
    //         // Get processor's ID with email
    //         $account = $this->supportAgent->getSupportAccount($email);
    //         // Check for query error
    //         if($account['success'] == false){
    //             // return $this->customResponse->is500Response($response,$account['data']);
    //             return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    //         }
    //         // Check if email is valid by seeing if account is found
    //         if($account['data'] == false){
    //             // return $this->customResponse->is500Response($response,$account['data']);
    //             return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token & email not found. Please sign into your account."));
    //         }
    // // Variables Group 1
    // // Get processor's account, role, & sup ID
    //     $processor_ID = $account['data']['id'];
    //     $processor_role = $account['data']['role_type'];
    //     $processor_supID = $account['data']['supervisor_id'];
    
    // // -----------------------------------
    // // Validation 1 - Check role of processor (request sender)
    //         // If the request sender is not a supervisor, manager or admin, deny request
    //         if($processor_role < 4){
    //              // return $this->customResponse->is500Response($response,$account['data']);
    //              return $this->customResponse->is404Response($response,$this->generateServerResponse(401, "Unauthorized Access: Please sign into an authorized account to process this request."));
    //         }
    
    // // -----------------------------------
    // // Auth SENDERS Information (JWT AUTH)
    // // -----------------------------------
    //         $auth_agent_result = $this->authenticate_agent($bearer_token, $processor_ID);
    //         if($auth_agent_result['success'] != 200){
    //             return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
    //         }
    
    // -----------------------------------
    // Get Notification Details using the notification ID
    // -----------------------------------
            $notificationObj = $this->supportTicket->getNotificationUsingNotificationID($notif_ID);
            // Check for query error
            if($notificationObj['success'] == false){
                // return $this->customResponse->is500Response($response,$notificationObj['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            } 
            // Check if data is found
            if($notificationObj['data'] == false){
                // return $this->customResponse->is500Response($response,$notificationObj['data']);
                return $this->customResponse->is404Response($response,$this->generateServerResponse(404, "An error occured while processing your request. The notification cannot be found. Please resfresh the browser and try again."));
            }
    // Variables Group 2
    // Get Support Ticket ID, Ticket Actions ID, notifications Type, 
    // permissions ID, permissions owner, is read
    $notification_data = $notificationObj['data'];
    $notif_creator_ID = $notification_data != null ? $notification_data['generated_by'] : null; // should be the same as the assigned agent in the ticket (if transfer request, but if override request should be different)
    $suport_ticket_ID = $notification_data != null ? $notification_data['support_ticket_id'] : null;
    $ticket_actions_ID = $notification_data != null ? $notification_data['ticket_actions_id'] : null;
    $notification_type_ID = $notification_data != null ? $notification_data['notification_type_id'] : null;
    $permissions_ID = $notification_data != null ? $notification_data['permissions_id'] : null;
    $permissions_owner_ID = $notification_data != null ? $notification_data['permissions_owner'] : null;
    $has_taken_action_on_notification = $notification_data != null ? $notification_data['has_taken_action'] : null;
    $has_notification_been_deleted = $notification_data != null ? $notification_data['is_deleted'] : null;
    $notification_sysgen = $notification_data != null ? $notification_data['system_generated_description'] : null;
    $notif_recipient_ID = $notification_data != null ? $notification_data['recipient_id'] : null;
    // // For Debugging purposes
    // //  $resData["notificationObj"] = $notificationObj['data'];
    // $resData["notification_data"] = $notification_data;
    // $resData["suport_ticket_ID"] = $suport_ticket_ID;
    // $resData["ticket_actions_ID"] = $ticket_actions_ID;
    // $resData["notification_type_ID"] = $notification_type_ID;
    // $resData["permissions_ID"] = $permissions_ID;
    // $resData["permissions_owner_ID"] = $permissions_owner_ID;
    // $resData["has_taken_action_on_notification"] = $has_taken_action_on_notification;
    // $resData["has_notification_been_deleted"] = $has_notification_been_deleted;
    // $resData["notification_sysgen"] = $notification_sysgen;
    

    // // $resData["transfer_ID"] = $transfer_reason_ID;
    // //  Note permissions_ID & permissions_owner_ID can be used to pull the override code data
    
    // // -----------------------------------
    // // Validate Ticket
    // // -----------------------------------
    //         // Does several checks, valid ID, valid status, if found etc.
    //         $validate_ticket_result = $this->validate_ticket($suport_ticket_ID,2,$processor_ID,$processor_role,null);
    //         if($validate_ticket_result['success'] != 200){
    //             return $this->return_server_response($response,$validate_ticket_result['error'],$validate_ticket_result['success']);
    //         }
    // // Variables Group 3
    // // Get Support Ticket base info, support ticket agent, support ticket role needed
    //         $support_ticket_base_info = $validate_ticket_result['data']['data'];
    //         $is_processor_the_ticket_owner = $validate_ticket_result['is_owner'];
    //         $is_processor_authorized = $validate_ticket_result['authorized']; // authorized to work on ticket
    //         $support_ticket_agent =  $support_ticket_base_info['assigned_agent'];
    //         $support_ticket_issue_ID = $support_ticket_base_info['issue_id'];
    //         $role_needed = $this->roleSubTypes[$support_ticket_issue_ID];
    // // // For Debugging purposes
    // // // $resData["validate_ticket_result"] = $validate_ticket_result;
    // // $resData["support_ticket_base_info"] = $support_ticket_base_info;
    // // $resData["is_processor_the_ticket_owner"] = $is_processor_the_ticket_owner;
    // // $resData["is_processor_authorized"] = $is_processor_authorized;
    // // $resData["support_ticket_agent"] = $support_ticket_agent; // should be same as notification creator (if transfer request, but if override request should be different)
    // // $resData["support_ticket_issue_ID"] = $support_ticket_issue_ID;
    // // $resData["role_needed"] = $role_needed;
    
    // -----------------------------------
    // Process Read Toggle
    // -----------------------------------
        // Has the notification already been processed ?
        if($has_taken_action_on_notification != 0){
            return $this->customResponse->is400Response($response,"Bad Request: This notification has already been processed.");
        }
        // Has the notification already been deleted ?
        if($has_notification_been_deleted != 0){
            return $this->customResponse->is400Response($response,"Bad Request: This notification has already been deleted.");
        }
        // // is this the same notification recipient ?
        // if($notif_recipient_ID != $processor_ID){
        //     return $this->customResponse->is401Response($response,"Token Expired! Please log into your account and try again!");
        // }
        // Proceed
            $toggleObj_notif = $this->supportTicket->toggleReadNotif($notif_ID);
            // Check for query error
            // if(!isset( $toggleObj_notif) ||  $toggleObj_notif['success'] == null ||  $toggleObj_notif['success'] == false){
            //     // return $this->customResponse->is500Response($response, $toggleObj_notif['data']);
            //     return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            // } 
            $resData['toggle_read'] = $toggleObj_notif['data'];
            $resData['message'] = "Succesfully toggled read!";

    return $this->return_server_response($response,"This route works",200,$resData); 
}





public function notifyManager(Request $request,Response $response, array $args)
{
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
    
    // -----------------------------------
    // Get Ticket Processer's Information (Request Sender)
    // -----------------------------------
            // Get processor's ID with email
            $account = $this->supportAgent->getSupportAccount($email);
            // Check for query error
            if($account['success'] == false){
                // return $this->customResponse->is500Response($response,$account['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }
            // Check if email is valid by seeing if account is found
            if($account['data'] == false){
                // return $this->customResponse->is500Response($response,$account['data']);
                return $this->customResponse->is401Response($response,$this->generateServerResponse(401, "JWT - Err 2: Token & email not found. Please sign into your account."));
            }
    // Variables Group 1
    // Get processor's account, role, & sup ID
        $processor_ID = $account['data']['id'];
        $processor_role = $account['data']['role_type'];
        $processor_supID = $account['data']['supervisor_id'];
    
    // -----------------------------------
    // Validation 1 - Check role of processor (request sender)
            // If the request sender is not a supervisor, manager or admin, deny request
            if($processor_role < 4){
                 // return $this->customResponse->is500Response($response,$account['data']);
                 return $this->customResponse->is401Response($response,$this->generateServerResponse(401, "Unauthorized Access: Please sign into an authorized account to process this request."));
            }
    
    // -----------------------------------
    // Auth SENDERS Information (JWT AUTH)
    // -----------------------------------
            $auth_agent_result = $this->authenticate_agent($bearer_token, $processor_ID);
            if($auth_agent_result['success'] != 200){
                return $this->return_server_response($response,$auth_agent_result['error'],$auth_agent_result['success']);
            }
            if($processor_supID == null){
                return $this->customResponse->is400Response($response,$this->generateServerResponse(400, "You are not currently assigned with a manager"));
            }
    // -----------------------------------
    // Check last Notify
    // -----------------------------------
            $resData = [];
            $notifyCheck = $this->supportTicket->checkTime_last_notifyManager($processor_supID,$processor_ID); 
            // // Check for query error
            if(!isset( $notifyCheck ) ||  $notifyCheck ['success'] == null ||  $notifyCheck['success'] == false){
                // return $this->customResponse->is500Response($response, $notifyManager['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            } 
            date_default_timezone_set('Asia/Manila');
            $lastTime = $notifyCheck['data'];
            // $resData['test'] = $lastTime;
            if($lastTime != false){
                $lastCheckSubmitted = $lastTime['created_on'];
                $to_time = strtotime("now");
                $from_time =  strtotime($lastCheckSubmitted);
                $diff = round(abs($to_time - $from_time) / 60,2);
                $timeCap = 5; // 5 min
                if(($diff < $timeCap)){
                    // $resData['past_time_cap'] = $diff > 5;
                    // $resData['diff' ] = $diff;
                    return $this->customResponse->is400Response($response,"Please wait 5 minutes before submitting another request!");
                }
            }
    // -----------------------------------
    // Send a Notify
    // -----------------------------------
            $notifyManager = $this->supportTicket->notifyManager( 
                $processor_supID,   // Manager ID
                $processor_ID              // Sup ID
            );
            // // Check for query error
            if(!isset( $notifyManager) ||  $notifyManager['success'] == null ||  $notifyManager['success'] == false){
                // return $this->customResponse->is500Response($response, $notifyManager['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            } 
            $resData['notifyManager'] = $notifyManager['data'];
            $resData["message"] = "Manager Notified!";
            // $resData["sup"] = $processor_supID;

    return $this->return_server_response($response,"This route works",200,$resData); 
}



// ===============================================
// ===============================================
//          JUN 15  
// ===============================================
// ===============================================
public function getTeamsAndAgents(Request $request,Response $response, array $args){
    // // Get the bearer token from the Auth header
    // $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // // // Get ticket parameter for ticket information
    // // $notif_ID = $args['notifID'];

    // // Get Agent Email for validation
    // $this->validator->validate($request,[
    //     // Check if empty
    //     "email"=>v::notEmpty(),
    // ]);
    //     // Returns a response when validator detects a rule breach
    //     if($this->validator->failed())
    //     {
    //         $responseMessage = $this->validator->errors;
    //         return $this->customResponse->is400Response($response,$responseMessage);
    //     }

    // // Store Params
    // $email = CustomRequestHandler::getParam($request,"email");

    // Get processor's ID with email
    $agentsList = $this->supportAgent->getTeamsAndAgents();
    // Check for query error
    if($agentsList['success'] == false){
        // return $this->customResponse->is500Response($response,$agentsList['data']);
        return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
    }

    $resData = [];
    $resData['agentsList'] = $agentsList['data'];
    // $resData['anouncement'] =  $announce['data'];

    return $this->customResponse->is200Response($response,$resData);
}


public function getReport(Request $request,Response $response, array $args){
    // Get the bearer token from the Auth header
    $bearer_token = JSON_encode($request->getHeader("Authorization"));
    // // Get ticket parameter for ticket information
    // $notif_ID = $args['notifID'];

    // Get Agent Email for validation
    $this->validator->validate($request,[
        // Check if empty
        "email"=>v::notEmpty(),
        "ticket_type"=>v::notEmpty(),
        "ticket_status"=>v::notEmpty(),
        "ticket_filter"=>v::notEmpty(),
        "ticket_time_period"=>v::notEmpty(),
        "date_start"=>v::notEmpty(),
        "date_end"=>v::notEmpty()
    ]);
        // Returns a response when validator detects a rule breach
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

    // Store Params
    $email = CustomRequestHandler::getParam($request,"email");
    $ticket_type = CustomRequestHandler::getParam($request,"ticket_type");
    $ticket_status = CustomRequestHandler::getParam($request,"ticket_status");
    $ticket_filter = CustomRequestHandler::getParam($request,"ticket_filter");
    $ticket_time_period = CustomRequestHandler::getParam($request,"ticket_time_period");
    $date_start = CustomRequestHandler::getParam($request,"date_start");
    $date_end = CustomRequestHandler::getParam($request,"date_end");
    
    // Clean data with defaults
        // 1-All (default), 2-Verification, 3-Support
        $ticket_type =  $ticket_type > 0 && $ticket_type < 4 ?  $ticket_type : 1;
        // 1-All (default), 2-New, 3-Ongoing , 4-Closed/resolved
        $ticket_type =  $ticket_type > 0 && $ticket_type < 5 ?  $ticket_type : 1;
        // 1-All (default), 2-By Team, 3-By Agent
        $ticket_type =  $ticket_type > 0 && $ticket_type < 4 ?  $ticket_type : 1;
        // 1-Monthly (default), 2-Weekly, 3-Daily
        $ticket_type =  $ticket_type > 0 && $ticket_type < 4 ?  $ticket_type : 1;

    // $resData = [];
    // $resData['anouncement'] =  $announce['data'];
    // $resData['myRole'] = $user_role;
    return $this->customResponse->is200Response($response,"This route works");
}












































































































































































































// Helper function to validate the transfer code
// params: agent_role, supportticket_issue
// returns: object with keys data & success
private function validate_override_code($code, $sup_id, $permissions_id, $sup_tkt_id = null){
    $retVal = [];

    if($sup_tkt_id != null){
        // Get from DB if a notification has already been sent for this type of request
        $notifications = $this->supportTicket->get_notifications($sup_id, $permissions_id, $sup_tkt_id);
        // Check for query error
        if($notifications['success'] == false){
            $retVal['success'] = 500;
            // $retVal['data'] = $trans_code_res['data'];
            $retVal['error'] = "SQLSTATE[42000]: Syntax error or access violation: Please check your query.";
            return $retVal;
        }
        // Check if a notification has already been submitted
        if(count($notifications['data']) != 0){
            $retVal['success'] = 400;
            // $retVal['data'] = $trans_code_res['data'];
            $retVal['error'] = "You have already submitted a transfer request. Please wait for the supervisor to process your request before submitting another one.";
            return $retVal;
        }
    }

    // Get from DB the hased value using sup_id & permissions code
    $trans_code_res = $this->supportTicket->get_transCode($sup_id, $permissions_id);
    // Check for query error
    if($trans_code_res['success'] == false){
        $retVal['success'] = 500;
        // $retVal['data'] = $trans_code_res['data'];
        $retVal['error'] = "SQLSTATE[42000]: Syntax error or access violation: Please check your query.";
        return $retVal;
    }

    // Check if not found
    if($trans_code_res['data'] == false){
        $retVal['success'] = 401;
        // $retVal['data'] = $trans_code_res['data'];
        $retVal['error'] = "Incorrect Transfer Code.";
        return $retVal;
    }

    // Check if code is void
    if($trans_code_res['data']['is_void'] == 1){
        $retVal['success'] = 400;
        // $retVal['data'] = $trans_code_res['data'];
        $retVal['error'] = "This transfer code is void and can no longer be used.";
        return $retVal;
    }

    $hashed_inDB = $trans_code_res['data']['override_code'];

    // Check if transfer code is correct

    $plain = openssl_decrypt($hashed_inDB, "AES-128-ECB", "WQu0rd4T");

    if($code != $plain){
        $retVal['success'] = 401;
        // $retVal['data'] = $trans_code_res['data'];
        $retVal['error'] = "Permission code provided is incorrect. Please check the entered code and try again.";
        return $retVal;
    }
    
    $retVal['success'] = 200;
    $retVal['error'] =  [];
    // $retVal['data'] =  $trans_code_res;
    return $retVal;
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
private function validate_ticket($p_ticket_id, $p_ticket_status = null, $p_agent_ID = null, $p_agent_role = null, $p_ticket_issue = null){
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

    // Check if the ticket has the correct issue
    if($p_ticket_issue != null && $p_ticket_issue != $base_info['data']['issue_id']){
        $retVal['success'] = 400;
        $retVal['error'] = "Bad Request: Incorrect ticket issue for this type of request.";
        return $retVal;
    }

    // Check if correct status
    $bi_stat = $base_info['data']['status'];
    if($p_ticket_status != null && $bi_stat != $p_ticket_status){
        $retVal['success'] = 400;
        // $retVal['data'] = $base_info['data'];
        $s_msg = ["This ticket has not been assigned to an agent yet.","This ticket is already assigned to an agent.","This ticket is already closed.", "This ticket is already closed."];
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
        case 200:
            return $this->customResponse->is200Response($r_res,  $formatted_res);
        break; 
        case 401:
            return $this->customResponse->is401Response($r_res,  $formatted_res);
        break; 
        case 404:
            return $this->customResponse->is404Response($r_res,  $formatted_res);
        break;  
        case 400:
            return $this->customResponse->is400Response($r_res,  $formatted_res);
        break; 
        default:
            return $this->customResponse->is500Response($r_res,  $formatted_res);
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
            // Get job order of ticket
            $job_order_result = $this->supportTicket->get_joborder_from_support_ticket($params['ticket_ID']);
            // Check for query error
            if($job_order_result['success'] == false){
                $resData['error'] = "SQLSTATE[42000]: Syntax error or access violation: Please check your query.";
                $resData['status'] = 500;
                // $resData['data'] = $job_order_result['data'];
                return  $resData;
            }
            // Return full ticket nbi info only if agent is authorized
            if( $params['authorized'] == true ){
                // Store info to return
                // $resData['data'] = $job_order_result["data"];
                // if ownership
                if($params['ownership'] != null && $params['ownership'] == true){
                    $resData['data'] = $job_order_result["data"];
                } else {
                    $data = $job_order_result["data"];
                    $limited_data = [];
                    $limited_data["job_order_id"] = $data["job_order_id"];
                    $limited_data["worker_id"] = $data["worker_id"];
                    $limited_data["worker_fname"] = $data["worker_fname"];
                    $limited_data["worker_lname"] = $data["worker_lname"];
                    $limited_data["homeowner_id"] = $data["homeowner_id"];
                    $limited_data["ho_fname"] = $data["ho_fname"];
                    $limited_data["ho_lname"] = $data["ho_lname"];
                    $limited_data["job_order_status_id"] = $data["job_order_status_id"];
                    $limited_data["job_order_status_text"] = $data["job_order_status_text"];
                    $limited_data["job_start"] = $data["job_start"];
                    $limited_data["job_end"] = $data["job_end"];
                    $limited_data["job_post_name"] = $data["job_post_name"];
                    $resData['data'] = $limited_data;
                }
            } else {
                $resData["job_order_info"] = null;
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