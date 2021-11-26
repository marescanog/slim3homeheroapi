<?php


namespace App\Controllers;

use App\Models\Worker;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class WorkerController
{
    protected  $customResponse;

    protected $worker;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->worker = new Worker();

        $this->validator = new Validator();
    }


    public function getRegistration_personalInfo(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Extract token by omitting "Bearer"
        $jwt = substr(trim($bearer_token),9);

        // Decode token to get user ID
        $result =  GenerateTokenController::AuthenticateUserType($jwt, 2);

        if($result['status'] !== true){
            return $this->customResponse->is401Response($response, $result['message'] );
        }

        $userID =  $result["data"]["jti"];
        $userData = [];

        // Get user from the databse
            // Get expeertise list
            $expertiseList = $this->worker->getList_expertise($userID);
            if($expertiseList["success"] == false){
                return $this->customResponse->is500Response($response, $expertiseList["data"] );
            }
            $userData["expertiseList"] = $expertiseList["data"];

            // Get rate and rate type
            $defaultRate = $this->worker->get_defaultRate_defaultRateType($userID);
            if($defaultRate["success"] == false){
                return $this->customResponse->is500Response($response, $defaultRate["data"] );
            }
            $userData["defaultRate_andType"] = $defaultRate["data"];

        // Authenticate user

        // If everything is good, return information needed for personal info page

        // return $this->customResponse->is200Response($response, $userID );


        //return $this->customResponse->is200Response($response,  );


        return $this->customResponse->is200Response($response,  $userData);
    }

    
}