<?php

namespace App\Models;

use App\Db\DB;
use PDO;
use PDOException;

class Support 
{
        // DB stuff
        private $table = 'support_agent';

        // Constructor 
        public function __construct(){
        }


    // @desc    Adds a user and a homeowner to the database
    // @params  phone number, first name, last name
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is
    public function createSupportAgent($first_name, $last_name, $phone_number, $password, $email, $role ){

        // Create Password Hash
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "BEGIN;
                    INSERT INTO hh_user(user_type_id, first_name, last_name, phone_no, password) 
                        values(:utypeid,:fname,:lname,:phone,:pass);
                    INSERT INTO ".$this->table." (id, email, role_type) VALUES (LAST_INSERT_ID(), :email, :role);
                    COMMIT;
                    ";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            if($role == 5 || $role == 6){
                $utypeID = 4;
            } else {
                $utypeID = 3;
            }
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':utypeid', $utypeID);
                $stmt->bindparam(':fname', $first_name);
                $stmt->bindparam(':lname', $last_name);
                $stmt->bindparam(':phone', $phone_number);
                $stmt->bindparam(':pass', $hashed_pass);
                $stmt->bindparam(':email', $email);
                $stmt->bindparam(':role', $role);
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




    // @desc    Retreives the support agent account details based on email
    // @params  email
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is the support account
    public function getSupportAccount($email){

        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT * FROM support_agent WHERE email=:EMAIL";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':EMAIL', $email);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
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


 // @desc    Retreives the support agent account details based on email
    // @params  email
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is the support account
    public function get_permission_codes($permissions_owner, $searchType = null){

        try{
            $db = new DB();
            $conn = $db->connect();

            $result = [];
            $sql = "";
            // CREATE query
            if($searchType == null){

            } else if ($searchType == 2) {

            } else {
                $sql = "SELECT * FROM override_codes oc WHERE oc.permissions_owner_id = :poid;";
            
                // Prepare statement
                $stmt =  $conn->prepare($sql);
                $result = "";
    
                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':poid', $permissions_owner);
                    $stmt->execute();
                    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
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




    // @desc    Retreives the support agent full name based on user id
    // @params  email
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is the support account
    public function get_user_name($userID){

        try{
            $db = new DB();
            $conn = $db->connect();

            $result = [];
            $sql = "";

            // CREATE query
            $sql = "SELECT hh.user_id, CONCAT(hh.last_name, ', ', hh.first_name) 
            as full_name, hh.first_name, hh.last_name 
            FROM hh_user hh 
            WHERE hh.user_id = :userID;";
        
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
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



    // @desc    Retreives the support agent account details based on email
    // @params  email
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is the support account
    public function get_trans_reasons(){

        try{
            $db = new DB();
            $conn = $db->connect();

            $result = [];
            $sql = "";
            // CREATE query
            $sql = "SELECT * FROM ticket_transfer_reason tr WHERE tr.is_deleted = 0;";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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










































































}