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
        $this->customResponse->is200Response($response, $resData['data']);
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
        case 1:
            // Registration
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

            // Get ticket assignment history
            $his = $this->supportTicket->get_ticket_history($base_info["data"]["id"]);
            // Check for query error
            if($his['success'] == false){
                // return $this->customResponse->is500Response($response,$his['data']);
                return $this->customResponse->is500Response($response,"SQLSTATE[42000]: Syntax error or access violation: Please check your query.");
            }

            // Store info to return
            $resData["base_info"] = $base_info["data"];
            $resData["history"] = $his["data"];
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
    $this->customResponse->is200Response($response, $resData);
}




    


}