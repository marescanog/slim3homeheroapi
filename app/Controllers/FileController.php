<?php


namespace App\Controllers;

use App\Models\File;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class FileController
{
    protected  $customResponse;

    protected $file;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->file = new File();

        $this->validator = new Validator();
    }


    public function upload(Request $request,Response $response){

    
    
         return $this->customResponse->is200Response($response, "This route works" );

    }

    
}