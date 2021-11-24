<?php


namespace App\Models;

require_once __DIR__ . '/../../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

class Storage
{

    private $projectID;

    private $storage;

    public function  __construct()
    {
        putenv($_ENV['GOOGLE_APPLICATION_CREDENTIALS'].'='.$_ENV['GOOGLE_CREDENTIALS']); // PROD
        //putenv('GOOGLE_APPLICATION_CREDENTIALS=google-credentials.json'); // PROD
        //putenv('GOOGLE_APPLICATION_CREDENTIALS=C:\\users\asus\Downloads\google-credentials.json'); // DEV

        $this->projectId = 'steel-fin-282304';

        $this->storage = new StorageClient([
            'projectID' => $this->projectID
        ]);
    }

    public function getProjectStorage(){

        return  $this->storage ;
    }


    public function createBucket($bucketName){

        # Creates the new bucket
        $bucket = $this->storage->createBucket($bucketName);

        return $bucket;
    }

    public function listBuckets(){
        $buckets = $this->storage->buckets();

        // $buckets = $buckets['items'];

        $bucket_arr = [];

        foreach($buckets as $bucket){
            array_push($bucket_arr, $bucket->name());
        }

        return $bucket_arr;
    }

    function uploadObject($bucketName, $objectName, $source)
    {
        // $bucketName = 'my-bucket';
        // $objectName = 'my-object';
        // $source = '/path/to/your/file';
    
        $storage = new StorageClient();
        $file = fopen($source, 'r');
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($file, [
            'name' => $objectName
        ]);
        printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);
    }
    
}