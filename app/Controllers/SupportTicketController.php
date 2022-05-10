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

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->supportTicket = new SupportTicket();

         $this->supportAgent = new Support();

         $this->validator = new Validator();
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
//  April 26 2022

// gets all info pn specified ticket
public function getInfo(Request $request,Response $response, array $args)
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

    // Check if the user is allowed to access the ticket
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

    // Reference for list of roles & their authorization
    /* 
        1 - Verification (Cann access only verification tickets)
        2 - Customer Support (Cann access both csx & verification tickets)
        3 - Technical Support (Cann access only tech tickets)
        4 - Supervisor  (Can access all types)
        5 - Admin (Can access all types)
        6 - Super Admin (Can access all types)
    */

    // Reference for list of tickets types
    /* 
        // $roleSubTypes = ["1,2,3","4,5,6,7,8,9,10,11","12,13,14,15,16"];
        // Registration/Verification, Customer Support,GEN, Technical Support
        // supervisor, admin, superadmin
    */

    $resData = [];

    switch($role){
        case 1: // Registration
            // Make sure the sub issue is within range
            if($base_info["data"]["issue_id"] < 0 || $base_info["data"]["issue_id"] > 3){
                return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "You are not authorized to access/view this ticket.") );
            }

            // Get ticket history
            $his = $this->supportTicket->get_ticket_history($base_info["data"]["id"]);
            // Check for query error
            if($his['success'] == false){
                // return $this->customResponse->is500Response($response,$his['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }

            // Get ticket nbi history
            $nbi = $this->supportTicket->get_nbi_info($base_info["data"]["id"]);
            // Check for query error
            if($nbi['success'] == false){
                // return $this->customResponse->is500Response($nbi,$his['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }

            // Store info to return
            $resData["base_info"] = $base_info["data"];
            $resData["history"] = $his["data"];
            $resData["nbi_info"] = $nbi["data"];
            break;
        case 2:
            break;
        case 3:
            break;
        case 4:
        case 5:
        case 6:
            break;
        default:
        return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $result['message']) );
            break;
    }

    // verify if user is allowed to access this ticket
    return $this->customResponse->is200Response($response, $resData);
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

    // After the authentication steps above
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
    $auth_agent_result = $this->authenticate_agent($this->customResponse, $bearer_token, $agent_ID);
    if($auth_agent_result['success'] != 200){
        return $this->return_server_response($this->customResponse,$response,$auth_agent_result['error'],$auth_agent_result['success']);
    }

    // -----------------------------------
    // Validate Ticket
    // -----------------------------------
    $validate_ticket_result = $this->validate_ticket($this->supportTicket, $ticket_id,2,$agent_ID);
    if($validate_ticket_result['success'] != 200){
        return $this->return_server_response($this->customResponse,$response,$validate_ticket_result['error'],$validate_ticket_result['success']);
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
                return $this->return_server_response($this->customResponse,$response,"An Error occured while approving the registration. Please try again.",500);
            }
        break;
        case 2:
            // Case Disapprove
            // $resData['type'] = 2;
            $denyRes = $this->supportTicket->update_worker_registration($agent_ID_currrent, $ticket_id, $worker_id, $nbi_id, 2,  $comment);
            if(    $denyRes["success"] == true){
                $resData['message'] = "Registration denied successfully.";
            } else {
                return $this->return_server_response($this->customResponse,$response,"An Error occured while denying the registration. Please try again.",500);
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
                return $this->return_server_response($this->customResponse,$response,"No comment provided. Please include a comment.",400);
            } else {
                $notifyRes = $this->supportTicket->comment($ticket_id, $agent_ID_currrent, $comment, 1);
                if($notifyRes["success"] == true){
                    $resData['message'] = "Customer notified and comment successfully added to the ticket.";
                } else {
                    return $this->return_server_response($this->customResponse,$response,"An Error occured while saving the comment. Please try again.",500);
                }
            }
        break;
        default:
            // Case Option is to just Comment on Ticket "Add Info"
            if($comment == null){
                return $this->return_server_response($this->customResponse,$response,"No comment provided. Please include a comment.",400);
            } else {
                $commentRes = $this->supportTicket->comment($ticket_id, $agent_ID_currrent, $comment);
                if($commentRes["success"] == true){
                    $resData['message'] = "Comment was successfully added to the ticket.";
                } else {
                    return $this->return_server_response($this->customResponse,$response,"An Error occured while saving the comment. Please try again.",500);
                }
            }
        break;
    }


    return $this->return_server_response($this->customResponse,$response,"This route works",200,$resData);   
}











    
























































// Helper function to perform ticket validation
// params: ticket_ID, supportTicket obj
// returns: object with keys data & success
private function validate_ticket($obj_support_ticket, $p_ticket_id, $p_ticket_status = null, $p_agent_ID = null){
    $retVal = [];

    // Extract from db
    $base_info = $obj_support_ticket->get_ticket_base_info($p_ticket_id);

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

    // Check if it is authorized agent who will perform validation
    if($p_agent_ID != null && $p_agent_ID != $base_info['data']['assigned_agent']){
        // Get agent details
        $retVal['success'] = 400;
        $retVal['error'] = "This agent is not authorized to access this ticket. Please login to the authorized account to modify ticket.";
        return $retVal;
    }

    $retVal['success'] = 200;
    $retVal['error'] =  [];
    $retVal['data'] =  $base_info;

    return $retVal;
}

// Helper function to authenticate agent
// params: email, supportTicket obj
// returns: object with keys data & success
public function authenticate_agent($a_customResponse,$a_bearer_token,$user_ID){
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
private function return_server_response($r_custom_response,$r_res,  $r_message = "",$r_code = 200, $r_data=null){
    $formatted_res = [];
    $formatted_res['status'] = $r_code;
    $formatted_res['message'] = $r_message;
    $formatted_res['data'] = $r_data;
    switch($r_code){
        case 500:
            return $r_custom_response->is500Response($r_res,  $formatted_res);
        break; 
        case 404:
            return $r_custom_response->is404Response($r_res,  $formatted_res);
        break;  
        case 400:
            return $r_custom_response->is400Response($r_res,  $formatted_res);
        break; 
        default:
            return $r_custom_response->is200Response($r_res,  $formatted_res);
        break;
    }
}


}