<?php


namespace App\Controllers;

use App\Models\Homeowner;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class HomeownerController
{
    protected  $customResponse;

    protected $homeowner;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->homeowner = new Homeowner();

        $this->validator = new Validator();
    }

    public function test(Request $request,Response $response){
        return $this->customResponse->is200Response($response,  "This route works");
    }

}