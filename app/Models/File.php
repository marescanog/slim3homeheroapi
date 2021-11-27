<?php

namespace App\Models;

use App\Db\DB;
use PDO;
use PDOException;

class File 
{
        // DB stuff
        private $table = 'file';

        // Constructor 
        public function __construct(){
        }


    // @desc    Adds a user and a homeowner to the database
    // @params  phone number, first name, last name
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is
    // public function createHomewner($first_name, $last_name, $phone_number, $password){

    //     // Create Password Hash
    //     $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

    //     try{
    //         $db = new DB();
    //         $conn = $db->connect();

    //         // CREATE query
    //         $sql = "BEGIN;
    //                 INSERT INTO hh_user(user_type_id, first_name, last_name, phone_no, password) 
    //                     values(:utypeid,:fname,:lname,:phone,:pass);
    //                 INSERT INTO ".$this->table." (id) VALUES (LAST_INSERT_ID());
    //                 COMMIT;
    //                 ";
            
    //         // Prepare statement
    //         $stmt =  $conn->prepare($sql);
    //         $utypeID = 1;
    //         // Only fetch if prepare succeeded
    //         if ($stmt !== false) {
    //             $stmt->bindparam(':utypeid', $utypeID);
    //             $stmt->bindparam(':fname', $first_name);
    //             $stmt->bindparam(':lname', $last_name);
    //             $stmt->bindparam(':phone', $phone_number);
    //             $stmt->bindparam(':pass', $hashed_pass);
    //             $result = $stmt->execute();

    //         }
    //         $stmt=null;
    //         $db=null;

    //         $ModelResponse =  array(
    //             "success"=>true,
    //             "data"=>$result
    //         );

    //         return $ModelResponse;

    //     } catch (\PDOException $e) {

    //         $ModelResponse =  array(
    //             "success"=>false,
    //             "data"=>$e->getMessage()
    //         );

    //         return $ModelResponse;
    //     }
    // }

}