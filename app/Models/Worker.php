<?php

namespace App\Models;

use App\Db\DB;
use PDO;
use PDOException;

class Worker 
{
        // DB stuff
        private $table = 'worker';

        // Constructor 
        public function __construct(){
        }


    // @desc    Adds a user and a homeowner to the database
    // @params  phone number, first name, last name
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is
    public function createWorker($first_name, $last_name, $phone_number, $hashed_pass, $mainCity = null, $skill = null ){
        // Note: password is already hashed

        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "BEGIN;
                    INSERT INTO hh_user (user_type_id, first_name, last_name, phone_no, password) VALUES (:utypeid,:fname,:lname,:phone,:pass);
                    INSERT INTO ".$this->table." (id, main_city) VALUES (LAST_INSERT_ID(), :main_city);
                    INSERT INTO schedule (id) VALUES (LAST_INSERT_ID());
                    COMMIT;
                    ";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            $utypeID = 2;
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':utypeid', $utypeID);
                $stmt->bindparam(':fname', $first_name);
                $stmt->bindparam(':lname', $last_name);
                $stmt->bindparam(':phone', $phone_number);
                $stmt->bindparam(':pass', $hashed_pass);
                $stmt->bindparam(':main_city', $mainCity);
                $result = $stmt->execute();
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$result
            );

            return $ModelResponse;

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }


    // @desc    Checks if a worker has completed registration
    // @params  phone number
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is a bool, true when user registration complete and false if not
    public function isWorkerRegistered($phone){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT h.user_id, h.phone_no, w.has_completed_registration 
            FROM hh_user h, worker w 
            WHERE h.user_id = w.id
            AND h.user_type_id = :utype
            AND h.phone_no = :phone;
            ";


            // Prepare statement
            $stmt =  $conn->prepare($sql);

            $utype = 2;

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->execute(['utype' =>$utype, 'phone' => $phone]); 
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$result
            );

            return $ModelResponse;

        }catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }
    
}