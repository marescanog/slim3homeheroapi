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
    public function searchProject($keyword){

        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT id, expertise, type from project_type";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                //$stmt->bindparam(':keyword', $keyword);
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }



    public function getCities(){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT id, city_name FROM `city`";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }

    public function getBarangays(){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT id, city_id, barangay_name FROM `barangay` ";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }

    public function getHomeTypes(){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT id, home_type_name FROM `home_type`";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }

    public function getUsersSavedAddresses($userID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            // $sql = "SELECT * FROM `home_details` WHERE homeowner_id = :userid";
            $sql = "SELECT hd.home_id, hd.homeowner_id, h.street_no, h.street_name
            FROM `home_details` hd, home h
            WHERE hd.homeowner_id = :userid
            and  hd.home_id = h.id";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userid', $userID );
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }

    public function getUserDefaultAddress($userID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT default_home_id FROM `homeowner` where id = :userid";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userid', $userID );
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }

    public function saveAddress($userID, $street_no, $street_name,  $barangay_id,  $home_type, $extra_address_info ){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SET @@session.time_zone = '+08:00'; 
            BEGIN;                
                INSERT INTO home (street_no, street_name, barangay_id, home_type) 
                values(:streetNo, :streetName, :barangayID, :homeType);

                INSERT INTO home_details (home_id, homeowner_id, extra_address_info)
                VALUES(LAST_INSERT_ID(), :userID, :extraAdd);
            COMMIT;
            ";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':streetNo', $street_no );
                $stmt->bindparam(':streetName', $street_name);
                $stmt->bindparam(':barangayID', $barangay_id );
                $stmt->bindparam(':homeType', $home_type );
                $stmt->bindparam(':userID', $userID );
                $stmt->bindparam(':extraAdd', $extra_address_info );
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }



    public function getLatestAddedHomeID($userID){

        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT home_id, homeowner_id FROM `home_details` WHERE homeowner_id = :userID ORDER BY created_on DESC";
            
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }



    public function saveProject(
                $userID,
                $home_id, 
                $job_size_id, 
                $required_expertise_id,
                $job_description, 
                $rate_offer,   
                $isExactSchedule,
                $rate_type_id, 
                $preferred_date_time, 
                $project_name
            ){
        try{
            $db = new DB();
            $conn = $db->connect();

            $sql = "SET @@session.time_zone = '+08:00'; INSERT INTO job_post (homeowner_id, home_id, job_size_id, required_expertise_id, job_post_status_id, job_description, rate_offer, rate_type_id, is_exact_schedule, preferred_date_time, job_post_name)
                    VALUES (:userID, :homeID, :jobSize, :expert, 1, :jobdesc, :rateoffer, :ratetype, :isexact, :prefdateTime, :jobPostName);";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID );
                $stmt->bindparam(':homeID', $home_id);
                $stmt->bindparam(':jobSize', $job_size_id );
                $stmt->bindparam(':expert', $required_expertise_id );
                $stmt->bindparam(':jobdesc',  $job_description );
                $stmt->bindparam(':rateoffer',$rate_offer );
                $stmt->bindparam(':ratetype', $rate_type_id );
                $stmt->bindparam(':isexact', $isExactSchedule );
                $stmt->bindparam(':prefdateTime', $preferred_date_time );
                $stmt->bindparam(':jobPostName', $project_name );
                $result = $stmt->execute();
            } else {
                $result = "prepare statement failed";
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$result
            );
            // ($userID, $home_id , $job_size_id, $required_expertise_id, $job_description  
            // ,$rate_offer, $rate_type_id, $preferred_date_time, $project_name)
            return $ModelResponse;

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }








    // Dec 3
    public function getOngoingProjects($userID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT jp.id, jp.home_id, CONCAT(h.street_no,' ', h.street_name, ', ', b.barangay_name, ', ', c.city_name,' city') as `complete_address`,  jp.job_size_id, jos.job_order_size, jp.required_expertise_id, pt.type as `project_type`, e.id as `expertise_id`, e.expertise, jp.job_post_status_id, jp.job_description, jp.rate_offer, jp.rate_type_id, rt.type as `rate_type`, jp.preferred_date_time, jp.job_post_name
            FROM `job_post` jp, home h, barangay b, city c, job_order_size jos, project_type pt, expertise e, rate_type rt
            WHERE
            jp.home_id = h.id
            AND h.barangay_id = b.id
            AND b.city_id = c.id
            AND jp.job_size_id = jos.id
            AND jp.required_expertise_id = pt.id
            AND pt.expertise = e.id
            AND jp.rate_type_id = rt.id
            AND jp.is_deleted = 0
            AND jp.preferred_date_time >= CURRENT_TIMESTAMP
            AND homeowner_id = :userid";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userid', $userID );
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }



    public function getSingleJobPost($jobPostID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT jp.id, jp.home_id, CONCAT(h.street_no,' ', h.street_name, ', ', b.barangay_name, ', ', c.city_name,' city') as `complete_address`,  jp.job_size_id, jos.job_order_size, jp.required_expertise_id, pt.type as `project_type`, e.id as `expertise_id`, e.expertise, jp.job_post_status_id, jp.job_description, jp.rate_offer, jp.rate_type_id, rt.type as `rate_type`, jp.preferred_date_time, jp.job_post_name, jp.cancellation_reason, jp.date_time_closed, jp.created_on, jp.homeowner_id
            FROM `job_post` jp, home h, barangay b, city c, job_order_size jos, project_type pt, expertise e, rate_type rt
            WHERE
            jp.home_id = h.id
            AND h.barangay_id = b.id
            AND b.city_id = c.id
            AND jp.job_size_id = jos.id
            AND jp.required_expertise_id = pt.id
            AND pt.expertise = e.id
            AND jp.rate_type_id = rt.id
            AND jp.is_deleted = 0
            AND jp.id = :postID";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':postID', $jobPostID);
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }


    public function getSingleJobOrder($jobPostID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT jo.id,jo.created_on as `assigned_on`, jo.worker_id,  CONCAT(u.first_name, ' ', u.last_name) as `assigned_worker`, jo.date_time_start, jo.date_time_closed
            FROM `job_post` jp, `job_order` jo
            JOIN hh_user u ON jo.worker_id = u.user_id
            WHERE jo.job_post_id = jp.id
            and jo.job_post_id = :postID";

            $result = "";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':postID', $jobPostID);
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }


    public function getSingleBill($jobOrderID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT b.id, bs.status, b.date_time_completion_paid, b.total_price_billed, b.created_on as `billed_on`, pm.id as `payment_method_id`, pm.payment_method
            FROM `bill` b, `bill_status` bs, `payment_method`  pm
            WHERE  b.bill_status_id = bs.id
            AND b.job_order_id = :joID
            AND b.payment_method_id = pm.id";

            $result = "";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':joID', $jobOrderID);
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

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }




    public function getSingleRating($jobOrderID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT * FROM `rating` WHERE job_order_id = :joID";

            $result = "";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':joID', $jobOrderID);
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

        } catch (\PDOException $e) {

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