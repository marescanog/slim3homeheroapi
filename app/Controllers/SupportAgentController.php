<?php


namespace App\Controllers;

use App\Models\Support;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SupportAgentController
{
    protected  $customResponse;

    protected $supportAgent;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->supportAgent = new Support();

        $this->validator = new Validator();
    }


    public function test(Request $request,Response $response){
        return $this->customResponse->is200Response($response,  "This route works");
    }

}