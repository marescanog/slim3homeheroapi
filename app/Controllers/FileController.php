<?php


namespace App\Controllers;

use App\Models\File;
use App\Models\Storage;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

require_once __DIR__ . '/../../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;


class FileController
{
    protected  $customResponse;

    protected $file;

    protected  $validator;

    protected  $storage;

    public function  __construct()
    {

        $this->customResponse = new CustomResponse();

        $this->file = new File();

        $this->validator = new Validator();

        $this->storage = new Storage();
    }


    public function upload(Request $request,Response $response){
        $files = $request->getUploadedFiles();
        // $myFile = $files['file']['name'];
        // $mySource = $files['file']['tmp_name'];
        // $uploadFileName = $myFile->getClientFilename();

        $myFile = $files['file'];

        $extension = pathinfo($myFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $this->storage->uploadObject('macaroniandcheesy',$filename , $myFile->file);

        // if (empty($files['file'])) {
        //     return $this->customResponse->is400Response($response, "No files have been sent");
        // }

        // $myFile = $files['file'];
        // if ($myFile->getError() === UPLOAD_ERR_OK) {
        //     $uploadFileName = $myFile->getClientFilename();
        //     //$myFile->moveTo('uploads/' . $uploadFileName);
        // }

        //  return $this->customResponse->is200Response($response, $uploadFileName  );



         return $this->customResponse->is200Response($response, $myFile->file );

        //  return $this->customResponse->is200Response($response, $myFile->getClientFilename()  );

    }

    // public function createBucket(Request $request,Response $response){

    //      putenv('GOOGLE_APPLICATION_CREDENTIALS=C:\\users\asus\Downloads\google-credentials.json'); // DEV

    //      $projectId = 'steel-fin-282304';

    //     $storage = new StorageClient([
    //         'projectID' => $projectId
    //     ]);

    //     # Creates the new bucket
    //     $bucket = $storage->createBucket("isthisavalidbucketname");

    //     $sd = 'Bucket ' . $bucket->name() . ' created.';


    //     // $result = $this->storage->createBucket("MacaroniAndcheesy");

    //     // $result = $this->storage->getProjectStorage();
        
    //     //  return $this->customResponse->is200Response($response,$this->storage );

    //             // return $this->customResponse->is200Response( $response,$result );


    //     return $this->customResponse->is200Response($response, $sd );

    // }

    public function createBucket(Request $request,Response $response){



       $bucket = $this->storage->createBucket("hellotherebucket");

               $sd = 'Bucket ' . $bucket->name() . ' created.';

       // $result = $this->storage->getProjectStorage();
       
       //  return $this->customResponse->is200Response($response,$this->storage );

               // return $this->customResponse->is200Response( $response,$result );


       return $this->customResponse->is200Response($response, $sd );

   }

//    public function getListOfBuckets(Request $request,Response $response){
//         // $buckets = $this->storage->listBuckets();

//         // return $this->customResponse->is200Response($response, $buckets );

//         putenv('GOOGLE_APPLICATION_CREDENTIALS=C:\\users\asus\Downloads\google-credentials.json'); // DEV

//              $projectId = 'steel-fin-282304';
    
//             $storage = new StorageClient([
//                 'projectID' => $projectId
//             ]);

//             $buckets = $storage->buckets();

//         return $this->customResponse->is200Response($response, $buckets  );
//    }


   public function getListOfBuckets(Request $request,Response $response){
    $buckets = $this->storage->listBuckets();

    return $this->customResponse->is200Response($response, $buckets );

    }


    
}