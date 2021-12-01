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

    private function generateServerResponse($status, $message){
        $response = [];
        $response['status'] = 401;
        $response['message'] = $message;
        return $response;
    }

    public function getRegistration_personalInfo(Request $request,Response $response){
        // Get the bearer token from the Auth header
        $bearer_token = JSON_encode($request->getHeader("Authorization"));

        // Extract token by omitting "Bearer"
        $jwt = substr(trim($bearer_token),9);

        // Decode token to get user ID
        $result =  GenerateTokenController::AuthenticateUserType($jwt, 2);

        if($result['status'] !== true){
            return $this->customResponse->is401Response($response, $this->generateServerResponse(401, $result['message']) );
        }

        $userID =  $result["data"]["jti"];
        $userData = [];

        // Authenticate user
            // Get user information from DB and check if user is deleted
            $result = $this->worker->is_deleted($userID);
            $isDeleted = intval($result["data"]["is_deleted"]) != 0;
            if($result["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $result["data"]) );
            }
            if($isDeleted){
                return $this->customResponse->is401Response($response, $this->generateServerResponse(401, "The user is not available since it was deleted from the database.") );
            }


        // Get user from the databse
            // Get expertise list
            $expertiseList = $this->worker->getList_expertise($userID);
            if($expertiseList["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $expertiseList["data"]) );
            }
            $userData["expertiseList"] = $expertiseList["data"];

            // Get rate and rate type
            $defaultRate = $this->worker->get_defaultRate_defaultRateType($userID);
            if($defaultRate["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $defaultRate["data"]) );
            }
            $userData["defaultRate_andType"] = $defaultRate["data"];

            // Get NBI information)
            $nbi_info = $this->worker->get_nbi_information($userID);
            if($nbi_info["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $nbi_info["data"]) );
            }
            $userData["nbi_information"] = $nbi_info["data"];

            // Get NBI file uploads
            $nbi_files = $this->worker->get_nbi_files($userID);
            if($nbi_files["success"] == false){
                return $this->customResponse->is500Response($response, $this->generateServerResponse(500, $nbi_files["data"]) );
            }
            $userData["nbi_files"] = $nbi_files["data"];


        // Return information needed for personal info page
        return $this->customResponse->is200Response($response,  $userData);
    }

    // 2. Get Job Postings (Restrict by worker's preferred city & skillset)
    // 3. Get Ongoing Job Orders (Restrict by worker id/ only logged in workers postings)
    // 4. Get Past Job Orders (Restrict by worker id/ only logged in workers postings & isCompleted)
    //    - One version includes cancelled job orders
    //    - Another version only includes successfully billed job orders
    // 5. Get Reviews (Restrict by worker id/ only logged in workers info)
    // 6. PUT Update NBI Info - Will be handled by Worker registration Route (So just use reuse the route used in registration)
    // 7. POST add Licesce & Certificate
    // 8. PUT/POST add Introduction
    // 9. PUT update information - uses a combination of functions from models
    // 10. PUT save featured projects
    // 11. PUT/POST add project photos (two routes are needed, one route is saving to the google cloud storage- currently there's only 1 route for save one photo and not multiple photos. The multiple photos is still pending, the other route will be your code to save information to DB)
    // 12. Get Worker Info  - For the account profile page, feel free to use the DB functions in the model or write your own
    // 13. Get/Save Services offered from DB  - Worker ( refer to Project type table and not expertise)

    
}