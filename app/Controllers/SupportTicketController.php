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
        $this->customResponse->is200Response($response,  "this route works");
    }

}