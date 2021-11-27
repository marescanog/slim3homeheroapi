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


}