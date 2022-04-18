<?php

namespace App\Controllers;

use App\Models\SupportTicket;
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

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->supportTicket = new SupportTicket();

         $this->validator = new Validator();
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











    


}