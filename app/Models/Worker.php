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

    // @desc    
    // @params  
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is 
    public function template(){
        try{
            
            $db = new DB();
            $conn = $db->connect();


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

    // @desc    gets an array of NBI files
    // @params  userID
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is 
    public function get_nbi_files($userID){
        try{
            
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT nf.file_id as id, f.file_name, f.file_type, f.file_path from NBI_files nf
                    JOIN NBI_information n ON nf.NBI_id = n.id
                    JOIN file f ON nf.file_id = f.id
                    WHERE n.worker_id = :userID
                    AND DATEDIFF(n.expiration_date, CURDATE()) BETWEEN 0 AND 360
                    AND nf.is_deleted = 0
                    AND f.is_deleted = 0
                    AND n.is_deleted = 0";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

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

    // @desc    Gets the data that checks if worker is deleted
    // @params  userID
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is a bool 0 if not deleted and 1 if deleted
    public function is_deleted($userID){
        try{
            
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT is_deleted FROM `worker` WHERE id=:userID";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
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

    // @desc this function gets the nbi information (nbi info id, clearance_no, expiration_date)
    // @params  userID
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is an object containing the values mentioned above, false if empty
    public function get_nbi_information($userID){
        try{
            
            $db = new DB();
            $conn = $db->connect();


            // CREATE query
            $sql = "SELECT nf.NBI_id as id, n.clearance_no, n.expiration_date
            FROM NBI_files nf
            JOIN NBI_information n ON nf.NBI_id = n.id
            WHERE n.worker_id = :userID
            AND nf.is_deleted = 0
            AND n.is_deleted = 0
            AND DATEDIFF(n.expiration_date, CURDATE()) BETWEEN 0 AND 360";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
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

    // @desc  gets the default rate and default rate type of worker
    // @params  userID
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is an object containing the default rate and default type, null if empty
    public function get_defaultRate_defaultRateType($userID){
        try{
            
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT id, default_rate, default_rate_type  
                    FROM worker 
                    WHERE id = :userID;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
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

    // @desc    Retreives from the database a list of expertise the user has, 
    //          note: Expertise is different from the sub category project_type
    // @params  userID
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is an assocarray of expertise (IDs, ex [{"id"=>4,"name"=>"gardening"},{"id"=>1,"name"=>"carpentry"}]), empty array if empty
    public function getList_expertise($userID){
        try{

            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT p.expertise as id, e.expertise as name 
                    FROM skillset s
                    JOIN project_type p ON s.skill = p.id 
                    JOIN expertise e ON e.id = p.expertise 
                    WHERE s.worker_id = :userID 
                    AND s.is_deleted = 0
                    AND p.is_deleted = 0
                    AND e.is_deleted = 0
                    GROUP BY e.id;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->execute();
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