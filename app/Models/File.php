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
            $sql = "SELECT hd.home_id, b.city_id, h.barangay_id, hd.homeowner_id, h.street_no, h.street_name, CONCAT(h.street_no,' ', h.street_name, ', ', b.barangay_name, ', ', c.city_name,' city') as `complete_address`, hd.extra_address_info, ht.home_type_name as 'home_type'
            FROM `home_details` hd, home h, barangay b, city c, home_type ht
            WHERE hd.homeowner_id = :userid
            AND h.barangay_id = b.id
            AND b.city_id = c.id
            AND  hd.home_id = h.id
            AND  h.home_type = ht.id
            AND h.is_deleted = 0
            AND hd.is_deleted = 0";

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
            AND jp.job_post_status_id = 1
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
            $sql = "SELECT jp.id, jp.home_id, CONCAT(h.street_no,' ', h.street_name, ', ', b.barangay_name, ', ', c.city_name,' city') as `complete_address`,  jp.job_size_id, jos.job_order_size, jp.required_expertise_id, pt.type as `project_type`, e.id as `expertise_id`, e.expertise, jp.job_post_status_id, jp.job_description, jp.rate_offer, jp.rate_type_id, rt.type as `rate_type`, jp.preferred_date_time, jp.job_post_name, jp.cancellation_reason, jp.date_time_closed, jp.created_on, jp.homeowner_id, jp.is_exact_schedule
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
            $sql = "SELECT jo.id,jo.created_on as `assigned_on`, jo.worker_id,  CONCAT(u.first_name, ' ', u.last_name) as `assigned_worker`, jo.date_time_start, jo.date_time_closed, jo.cancelled_by, jo.homeowner_id, jo.order_cancellation_reason 
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





    // Dec 3
    public function getClosedProjects($userID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT jp.id, jp.home_id, CONCAT(h.street_no,' ', h.street_name, ', ', b.barangay_name, ', ', c.city_name,' city') as `complete_address`, jp.job_size_id, jos.job_order_size, jp.required_expertise_id, pt.type as `project_type`, e.id as `expertise_id`, e.expertise, jp.job_post_status_id, jp.cancellation_reason,jp.job_description, jp.rate_offer, jp.rate_type_id, rt.type as `rate_type`, jp.preferred_date_time, jp.job_post_name, jo.id as `job_order_id`, jo.isRated, r.overall_quality, r.professionalism, r.reliability, r.punctuality, r.comment, CONCAT(u.first_name, ' ' ,u.last_name) as `assigned_to`, jo.job_order_status_id, bill.bill_status_id, bill.total_price_billed, bill.date_time_completion_paid, jo.order_cancellation_reason, jo.cancelled_by, jo.homeowner_id as `homeowner_id`
            FROM home h, barangay b, city c, job_order_size jos, project_type pt, expertise e, rate_type rt, job_post jp
            LEFT JOIN job_order jo on jp.id = jo.job_post_id 
            LEFT JOIN rating r on jo.id = r.job_order_id 
            LEFT JOIN hh_user u on jo.worker_id = u.user_id
            LEFT JOIN bill on jo.id = bill.job_order_id
            WHERE jp.home_id = h.id
            AND h.barangay_id = b.id
            AND b.city_id = c.id
            AND jp.job_size_id = jos.id
            AND jp.required_expertise_id = pt.id
            AND pt.expertise = e.id
            AND jp.rate_type_id = rt.id
            AND jp.is_deleted = 0
            AND(
            	jp.job_post_status_id = 4 OR jo.job_order_status_id = 3 OR jo.job_order_status_id = 2 OR
                (jp.job_post_status_id = 1 AND jp.preferred_date_time < CURRENT_TIMESTAMP)
            )
            AND jp.homeowner_id = :userid";

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








    public function getOngoingJobOrders($userID){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT jp.id, jp.home_id, CONCAT(h.street_no,' ', h.street_name, ', ', b.barangay_name, ', ', c.city_name,' city') as `complete_address`, jp.job_size_id, jos.job_order_size, jp.required_expertise_id, pt.type as `project_type`, e.id as `expertise_id`, e.expertise, jp.job_post_status_id, jp.job_description, jp.rate_offer, jp.rate_type_id, rt.type as `rate_type`, jp.preferred_date_time, jp.job_post_name, jo.id as `job_order_id`, CONCAT(u.first_name, ' ' ,u.last_name) as `assigned_to`, jo.job_order_status_id, jo.date_time_start, u.phone_no
            FROM home h, barangay b, city c, job_order_size jos, project_type pt, expertise e, rate_type rt, job_post jp
            LEFT JOIN job_order jo on jp.id = jo.job_post_id 
            LEFT JOIN hh_user u on jo.worker_id = u.user_id
            WHERE jp.home_id = h.id
            AND h.barangay_id = b.id
            AND b.city_id = c.id
            AND jp.job_size_id = jos.id
            AND jp.required_expertise_id = pt.id
            AND pt.expertise = e.id
            AND jp.rate_type_id = rt.id
            AND jp.is_deleted = 0
            AND jp.job_post_status_id != 1
            AND jo.job_order_status_id = 1
            AND jp.homeowner_id = :userid";

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




// Dec 7

public function updateProject(
    $post_id,
    $home_id,
    $job_size_id,
    $job_description,
    $rate_offer,
    $rate_type_id,
    $preferred_date_time,
    $job_post_name
){
    try{

        $db = new DB();
        $conn = $db->connect();

        $sql = "UPDATE job_post jp
        SET 
            jp.home_id = :homeID, 
            jp.job_size_id = :jobSizeID, 
            jp.job_description = :jobDesc, 
            jp.rate_offer = :rateOffer, 
            jp.rate_type_id = :rateType, 
            jp.preferred_date_time = :prefDateTime,
            jp.job_post_name = :jobPostName 
        WHERE jp.id = :id";

        // Prepare statement
        $stmt =  $conn->prepare($sql);

        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':id', $post_id );
            $stmt->bindparam(':homeID', $home_id );
            $stmt->bindparam(':jobSizeID',  $job_size_id);
            $stmt->bindparam(':jobDesc', $job_description );
            $stmt->bindparam(':rateOffer', $rate_offer );
            $stmt->bindparam(':rateType',  $rate_type_id );
            $stmt->bindparam(':prefDateTime',$preferred_date_time );
            $stmt->bindparam(':jobPostName', $job_post_name );
            $result = $stmt->execute();
        } else {
            $result = "prepare statement failed";
        }
        $stmt=null;
        $db=null;

        // // For Debugging purposes
        // $formData = [];
        // $formData['id'] = $post_id;
        // $formData['home_id'] = $home_id;
        // $formData['job_size_id'] = $job_size_id;
        // $formData['job_description'] = $job_description;
        // $formData['rate_offer'] =  $rate_offer;
        // $formData['rate_type_id'] =   $rate_type_id;
        // $formData['preferred_date_time'] = $preferred_date_time;
        // $formData['job_post_name'] = $job_post_name;
        // $ModelResponse =  array(
        //     "success"=>true,
        //     "data"=>$formData
        // );

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


public function getJobPostUserID($jobPostID){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT jp.id, jp.homeowner_id     
        FROM job_post jp
        WHERE jp.id = :postID";

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





public function cancelJobPost($jobPostID, $reason){
    try{
        date_default_timezone_set('Asia/Singapore');
        $date = date('Y-m-d H:i:s');

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "UPDATE job_post jp 
        SET 
        jp.job_post_status_id = 4, 
        jp.date_time_closed = :currentTime, 
        jp.cancellation_reason =  :reason
        WHERE jp.id = :postID;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':postID', $jobPostID);
            $stmt->bindparam(':currentTime',  $date);
            $stmt->bindparam(':reason', $reason);
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


// =================================================

// DEC 8



public function getJobOrderUserID($jobOrderID){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT jo.id, jo.homeowner_id, jo.job_post_id, jo.worker_id  
        FROM job_order jo
        WHERE jo.id = :jobOrderID";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        
        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':jobOrderID', $jobOrderID);
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



public function cancelJobOrder($jobOrderID, $reason, $userID){
    try{
        date_default_timezone_set('Asia/Singapore');
        $date = date('Y-m-d H:i:s');

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "UPDATE job_order jo 
        SET 
        jo.job_order_status_id = 3, 
        jo.date_time_closed = :currentTime, 
        jo.order_cancellation_reason =  :reason,
        jo.cancelled_by =  :userID
        WHERE jo.id = :jobOrderID;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':jobOrderID', $jobOrderID);
            $stmt->bindparam(':currentTime',  $date);
            $stmt->bindparam(':reason', $reason);
            $stmt->bindparam(':userID', $userID);
            $result = $stmt->execute();
        }
        $stmt=null;
        $db=null;

        // For debugging purposes
        // $result = [];
        // $result['jobOrder_id'] = $jobOrderID;
        // $result['date'] = $date;
        // $result['reason'] = $reason;
        // $result['userID'] = $userID;

        // For debugging purposes
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





public function cancelOrder_DuplicatePost(
    $jobOrderID, 
    $reason, 
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
        if ( $jobOrderID == null){
            $ModelResponse =  array(
                "success"=>false,
                "data"=>"SQL Error: Job order ID not specified."
            );
            return $ModelResponse;
        }

        date_default_timezone_set('Asia/Singapore');
        $date = date('Y-m-d H:i:s');

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "
        SET @@session.time_zone = '+08:00';
        BEGIN;
        UPDATE job_order jo 
        SET 
        jo.job_order_status_id = 3, 
        jo.date_time_closed = :currentTime, 
        jo.order_cancellation_reason =  :reason,
        jo.cancelled_by =  :userID
        WHERE jo.id = :jobOrderID;

        INSERT INTO job_post (homeowner_id, home_id, job_size_id, required_expertise_id, job_post_status_id, job_description, rate_offer, rate_type_id, is_exact_schedule, preferred_date_time, job_post_name)
        VALUES (:userID, :homeID, :jobSize, :expert, 1, :jobdesc, :rateoffer, :ratetype, :isexact, :prefdateTime, :jobPostName);
        COMMIT;
        ";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':jobOrderID', $jobOrderID);
            $stmt->bindparam(':currentTime',  $date);
            $stmt->bindparam(':reason', $reason);
            $stmt->bindparam(':userID', $userID);
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
        }
        $stmt=null;
        $db=null;

        // // For debugging purposes
        // $result = [];
        // $result['jobOrder_id'] = $jobOrderID;
        // $result['date'] = $date;
        // $result['reason'] = $reason;
        // $result['userID'] = $userID;
        // //
        // $result['home_id'] = $home_id;
        // $result['job_size_id'] = $job_size_id ;
        // $result['required_expertise_id'] = $required_expertise_id;
        // $result['job_description'] = $job_description ;
        // $result['rate_offer'] = $rate_offer;
        // $result['rate_type_id'] = $rate_type_id;
        // $result['isExactSchedule'] = $isExactSchedule;
        // $result['preferred_date_time'] = $preferred_date_time ;
        // $result['project_name'] = $project_name;

        // For debugging purposes
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





public function checkJobOrderIssues($order_id)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT ji.job_order_id, ji.support_ticket_id, st.issue_id, stsub.subcategory, st.status as `status_id`, ststat.status, st.assigned_agent, st.last_updated_on, st.author_Description, st.created_on
        FROM job_order_issues ji, support_ticket st, support_ticket_subcategory stsub, support_ticket_status ststat
        WHERE job_order_id = :jobOrderID 
        AND ji.support_ticket_id = st.id
        AND st.issue_id = stsub.id
        AND st.status = ststat.id
        AND ji.is_deleted = 0;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':jobOrderID', $order_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $stmt = null;
        $db = null;

        $ModelResponse =  array(
            "success" => true,
            "data" => $result
        );

        return $ModelResponse;
    } catch (\PDOException $e) {

        $ModelResponse =  array(
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}







    public function getSupportTicketLastAction($support_ticket_ID)
    {
        try {

            $result = "";

            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT t.id, t.action_taken, a.action, t.system_generated_description, t.action_date
            FROM ticket_actions t, action_items a
            WHERE t.support_ticket = :supportTicketID
            AND t.action_taken = a.id
            ORDER BY t.id DESC;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':supportTicketID', $support_ticket_ID);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            $stmt = null;
            $db = null;

            $ModelResponse =  array(
                "success" => true,
                "data" => $result
            );

            return $ModelResponse;
            
        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }





    public function createJobIssueTicket(
        $author, 
        $subcategory, 
        $authorDescription, 
        $systemDescription, 
        $hasImages = 0,
        $order_id
    ){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = " SET @@session.time_zone = '+08:00'; 
                        BEGIN;
                        INSERT INTO support_ticket (author, issue_id, system_Description, author_Description, has_Images) 
                        values(:author,:issueID, :sysDesc, :authDesc, :hasImages);

                        SET @supportTicketID:=LAST_INSERT_ID();

                        INSERT INTO ticket_actions (action_taken, system_generated_description, support_ticket)
                            VALUES(1, :sysDesc, @supportTicketID);

                        INSERT INTO job_order_issues (job_order_id, support_ticket_id) values (:jobOrderID, @supportTicketID);

                        COMMIT;
                    ";

            // $sql = "
            //             INSERT INTO support_ticket (author, issue_id, system_Description, author_Description, has_Images) 
            //                 values(:author,:issueID, :sysDesc, :authDesc, :hasImages);
            // ";

            
            //Create also an action in the table
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':author', $author);
                $stmt->bindparam(':issueID', $subcategory);
                $stmt->bindparam(':sysDesc', $systemDescription);
                $stmt->bindparam(':authDesc', $authorDescription);
                $stmt->bindparam(':hasImages',  $hasImages);
                $stmt->bindparam(':jobOrderID',   $order_id);
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
    

public function updatePostSchedule($post_id, $preferred_date_time){
        try {

            $db = new DB();
            $conn = $db->connect();

            $sql = "UPDATE job_post jp 
            SET 
            jp.preferred_date_time = :newSched 
            WHERE jp.id = :postID;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':newSched', $preferred_date_time);
                $stmt->bindparam(':postID', $post_id);
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
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
}


public function completeCashPayment($order_id){
        try {

            date_default_timezone_set('Asia/Singapore');
            $date = date('Y-m-d H:i:s');

            $db = new DB();
            $conn = $db->connect();

            $sql = "UPDATE bill b 
            SET 
            b.bill_status_id = 2, 
            date_time_completion_paid = :currentDate
            WHERE b.job_order_id = :orderID;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':orderID', $order_id);
                $stmt->bindparam(':currentDate', $date);
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
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }


    public function hasRating($order_id){
        try {
            $result = "";

            $sql = "SELECT * FROM rating r where r.job_order_id = :jobOrderID";

            $db = new DB();
            $conn = $db->connect();
            
            //Create also an action in the table
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':jobOrderID', $order_id);
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

            return $ModelResponse;
        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }


    public function saveRating(
                        $order_id, 
                        $userID, 
                        $workerID, 
                        $quality,
                        $professionalism,
                        $reliability,
                        $punctuality,
                        $comment
                    ){
        try {
            $result = "";
            $db = new DB();
            $conn = $db->connect();


            $sql = "
            BEGIN;
                INSERT INTO rating (job_order_id, created_by, rated_worker, overall_quality, professionalism, reliability, punctuality, comment) 
                values(:jobOrderID, :createdBy, :workerID, :qual, :prof, :rel, :punct, :comm);
                
                UPDATE job_order jo SET jo.isRated = 1 WHERE jo.id = :jobOrderID;
            COMMIT;
            ";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':jobOrderID', $order_id);
                $stmt->bindparam(':createdBy', $userID);
                $stmt->bindparam(':workerID', $workerID);
                $stmt->bindparam(':qual', $quality);
                $stmt->bindparam(':prof', $professionalism);
                $stmt->bindparam(':rel', $reliability);
                $stmt->bindparam(':punct', $punctuality);
                $stmt->bindparam(':comm', $comment);
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
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }





    public function checkBillingIssues($bill_id){
    try {

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT bi.bill_id, bi.support_ticket_id, st.issue_id, stsub.subcategory, st.status as `status_id`, ststat.status, st.assigned_agent, st.last_updated_on, st.author_Description, st.created_on
        FROM bill_issues bi, support_ticket st, support_ticket_subcategory stsub, support_ticket_status ststat
        WHERE bi.bill_id = :billID 
        AND bi.support_ticket_id = st.id
        AND st.issue_id = stsub.id
        AND st.status = ststat.id
        AND bi.is_deleted = 0;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':billID', $bill_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $stmt = null;
        $db = null;

        $ModelResponse =  array(
            "success" => true,
            "data" => $result
        );

        return $ModelResponse;
    } catch (\PDOException $e) {

        $ModelResponse =  array(
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}



public function createBillingIssueTicket(
    $author, 
    $subcategory, 
    $authorDescription, 
    $systemDescription, 
    $hasImages = 0,
    $bill_id
){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = " SET @@session.time_zone = '+08:00'; 
                    BEGIN;
                    INSERT INTO support_ticket (author, issue_id, system_Description, author_Description, has_Images) 
                    values(:author,:issueID, :sysDesc, :authDesc, :hasImages);

                    SET @supportTicketID:=LAST_INSERT_ID();

                    INSERT INTO ticket_actions (action_taken, system_generated_description, support_ticket)
                        VALUES(1, :sysDesc, @supportTicketID);

                    INSERT INTO bill_issues (bill_id, support_ticket_id) values (:billID, @supportTicketID);

                    COMMIT;
                ";

        
        //Create also an action in the table
        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':author', $author);
            $stmt->bindparam(':issueID', $subcategory);
            $stmt->bindparam(':sysDesc', $systemDescription);
            $stmt->bindparam(':authDesc', $authorDescription);
            $stmt->bindparam(':hasImages',  $hasImages);
            $stmt->bindparam(':billID',   $bill_id);
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


// =========================================================================================
// Dec 11

public function getAccInfo($userID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT h.user_id, h.first_name, h.last_name, h.phone_no, h.created_on  FROM hh_user h WHERE h.user_id = :userID;";

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
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}



public function getProfilePic($userID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT p.file_id, p.file_path
        FROM profile_pics p 
        WHERE user_id = :userID
        AND is_deleted = 0
        AND is_current_used = 1
        ORDER BY created_on DESC
        LIMIT 1";

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
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}



public function getTotalJobPosts($userID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT COUNT(*) AS total_job_posts 
        FROM job_post
        WHERE homeowner_id = :userID
        AND is_deleted = 0;";

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
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}




public function getTotalCompletedProjects($userID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT COUNT(*) as `total_completed_projects` 
        FROM job_order jo
        WHERE jo.homeowner_id = :userID
        AND jo.is_deleted = 0
        and jo.job_order_status_id = 2;";

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
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}





public function getTotalCancelledProjects($userID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT COUNT(*) as `total_cancelled_projects`
        FROM job_post jp
        LEFT JOIN job_order jo ON  jp.id =  jo.job_post_id
        WHERE jp.homeowner_id = :userID
        AND (jo.job_order_status_id = 3 OR jp.job_post_status_id = 4)";

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
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}



public function getMostPostedCategory($userID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT e.expertise, count(jp.id) as totalCount
        FROM job_post jp, project_type pt, expertise e
        WHERE homeowner_id = :userID
        AND jp.required_expertise_id = pt.id
        AND e.id = pt.expertise
        GROUP BY e.id
        ORDER BY totalCount DESC
        LIMIT 1;";

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
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}




// DEC 11 NIGHT TIME


public function getSingleAddress($homeID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $sql = "SELECT h.id, h.street_no, h.street_name, h.barangay_id, h.home_type, hd.homeowner_id, hd.extra_address_info, b.city_id
        FROM home h, home_details hd, barangay b 
        WHERE h.id = :homeID
        AND h.id = hd.home_id
        AND h.barangay_id = b.id;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':homeID', $homeID);
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
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}



public function updateAddress($userID, $street_no, $street_name,  $barangay_id,  $home_type, $extra_address_info, $homeID ){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = " 
        BEGIN;                
            UPDATE home h SET street_no = :streetNo, street_name = :streetName, barangay_id = :barangayID, home_type = :homeType WHERE h.id = :homeID;

            UPDATE home_details hd SET hd.extra_address_info = :extraAdd
            WHERE hd.home_id = :homeID2 AND hd.homeowner_id = :userID;
        COMMIT;
        ";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";
        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':streetNo', $street_no );
            $stmt->bindparam(':streetName', $street_name);
            $stmt->bindparam(':barangayID', $barangay_id );
            $stmt->bindparam(':homeType', $home_type );
            $stmt->bindparam(':homeID', $homeID );
            $stmt->bindparam(':extraAdd', $extra_address_info );
            $stmt->bindparam(':homeID2', $homeID );
            $stmt->bindparam(':userID', $userID );
            $result = $stmt->execute();
        } else {
            $result = "PDO Error";
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






public function deleteAddress($userID, $homeID){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = " 
        BEGIN;                
            UPDATE home h SET h.is_deleted = 1 WHERE h.id = :homeID;

            UPDATE home_details hd SET hd.is_deleted = 1
            WHERE hd.home_id = :homeID2 AND hd.homeowner_id = :userID;
        COMMIT;
        ";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";
        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':homeID', $homeID );
            $stmt->bindparam(':homeID2', $homeID );
            $stmt->bindparam(':userID', $userID );
            $result = $stmt->execute();
        } else {
            $result = "PDO Error";
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


// ===================================================
// DEC 12


public function updateUserName($userID, $firstName, $lastName){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "UPDATE hh_user hh SET hh.first_name = :fname, hh.last_name = :lname WHERE hh.user_id = :userID;";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";
        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':fname', $firstName );
            $stmt->bindparam(':lname', $lastName );
            $stmt->bindparam(':userID', $userID );
            $result = $stmt->execute();
        } else {
            $result = "PDO Error";
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



public function saveProfilePicFileLocation($userID, $file_location){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "BEGIN;
                    UPDATE profile_pics p SET p.is_current_used = 0, p.is_deleted = 1 WHERE p.user_id = :userID;
                    INSERT INTO profile_pics (user_id, file_path) VALUES(:userID2, :fpath);
                COMMIT;
                ";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";
        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':userID', $userID );
            $stmt->bindparam(':fpath', $file_location );
            $stmt->bindparam(':userID2', $userID );
            $result = $stmt->execute();
        } else {
            $result = "PDO Error";
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





public function getAllWorkers(){
    try{

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        // $sql = "SELECT * FROM worker";

        $sql = "SELECT h.user_id, h.first_name, h.last_name, wr.default_rate, wr.default_rate_type, SUM(if(jo.job_order_status_id = 2, 1, 0)) as 'completed_jobs', AVG((r.overall_quality+r.professionalism+r.reliability+r.punctuality)/4) as `rating_average`, count(r.job_order_id) as `total_ratings`
        FROM hh_user h
        LEFT JOIN job_order jo ON h.user_id = jo.worker_id
        LEFT JOIN  rating r ON jo.id = r.job_order_id
        LEFT JOIN worker wr ON wr.id = h.user_id
        WHERE h.user_type_id = 2
        AND h.user_status_id = 2
        GROUP BY h.user_id;";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $result = "PDO Error";
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




public function cityPreferencePerWorker(){
    try{

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        // $sql = "SELECT * FROM worker";

        $sql = "SELECT cp.worker_id, GROUP_CONCAT(c.city_name, ' ') as cities 
        FROM `city_preference` cp, city c
        WHERE c.id = cp.city_id
        GROUP BY cp.worker_id
        ;";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $result = "PDO Error";
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





public function skillsetPerWorker(){
    try{

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        // $sql = "SELECT * FROM worker";

        $sql = "SELECT s.worker_id, GROUP_CONCAT(pt.type, ' ') as skills
        FROM project_type pt, `skillset` s
        LEFT JOIN worker w ON w.id = s.worker_id
        WHERE s.skill = pt.id
        GROUP BY s.worker_id
        ;";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $result = "PDO Error";
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




public function profilepicPerWorker(){
    try{

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        // $sql = "SELECT * FROM worker";

        $sql = "SELECT h.user_id, p.file_path
        FROM hh_user h 
        LEFT JOIN profile_pics p ON p.user_id = h.user_id
        WHERE h.user_type_id = 2
        AND h.user_status_id = 2
        AND p.is_current_used = 1
        ;";
        
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $result = "PDO Error";
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


// Dec 14

    public function hasWorkerBeenNotifiedOfProject($userID, $workerID, $projectID){
        try {
            $result = "";
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT * 
            FROM homeowner_notification h 
            WHERE h.homeowner_id = :userID
            AND h.worker_id = :workerID
            AND h.post_id = :projectID
            ;";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID );
                $stmt->bindparam(':workerID', $workerID );
                $stmt->bindparam(':projectID', $projectID );
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $result = "PDO Error";
            }

            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success" => true,
                "data" => $result
            );

            return $ModelResponse;
        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }




    public function saveHomeownerNotifcation($userID, $workerID, $projectID){
        try {
            $result = "";
            $db = new DB();
            $conn = $db->connect();
            
            // CREATE query
            $sql = "INSERT INTO homeowner_notification (homeowner_id, worker_id, post_id)
                    VALUES(:userID, :workerID, :projectID);";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID );
                $stmt->bindparam(':workerID', $workerID );
                $stmt->bindparam(':projectID', $projectID );
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $result = "PDO Error";
            }

            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success" => true,
                "data" => $result
            );

            return $ModelResponse;
        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }




    public function checkHomeownerNotifcationStatus($userID, $workerID, $projectID){
        try {
            $result = "";
            $db = new DB();
            $conn = $db->connect();
            
            // CREATE query
            $sql = "SELECT * FROM 
            homeowner_notification  hn, worker_decline_post wd
            WHERE hn.homeowner_id = :userID
            AND hn.post_id = :projectID
            AND hn.worker_id = :workerID
            AND hn.worker_id = wd.worker_id
            AND hn.post_id = wd.post_id";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID );
                $stmt->bindparam(':workerID', $workerID );
                $stmt->bindparam(':projectID', $projectID );
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $result = "PDO Error";
            }

            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success" => true,
                "data" => $result
            );

            return $ModelResponse;
        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }



// Schedule matching is discontinued for now
    public function getWorkerSchedulePreference($workerID){
        try {
            $result = "";
            $db = new DB();
            $conn = $db->connect();
            
            // CREATE query
            $sql = "SELECT * 
            FROM schedule s, worker w
            WHERE s.id = w.id 
            AND s.id = :workerID;";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':workerID', $workerID );
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $result = "PDO Error";
            }

            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success" => true,
                "data" => $result
            );

            return $ModelResponse;

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );

            return $ModelResponse;
        }
    }



//  Dec 15

public function getProjectTypes(){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT id, expertise, type from project_type";
        
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


public function getProjectCategories(){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "SELECT id, expertise from expertise";
        
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


public function getCode($supportID,$permissionsID)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $result="";

        // CREATE query
        $sql = "SELECT * FROM `override_codes` oc WHERE oc.permissions_owner_id = :supportID AND oc.permissions_id = :permissionsID";
        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':supportID', $supportID);
            $stmt->bindparam(':permissionsID', $permissionsID);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            $result = "PDO Error";
        }

        $stmt = null;
        $db = null;

        $ModelResponse =  array(
            "success" => true,
            "data" => $result
        );

        return $ModelResponse;
    } catch (\PDOException $e) {

        $ModelResponse =  array(
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}


public function updateCode($supportID,$createdBy,$permissionsID,$codeHashed)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $result="";
        
        // CREATE query
        $sql = "UPDATE override_codes oc 
        SET oc.override_code = :hashedCode,
        oc.updated_on =  now(),
        oc.created_by = :createdBy
        WHERE oc.permissions_owner_id = :supID AND oc.permissions_id = :perID;";

        // // Prepare statement
        $stmt =  $conn->prepare($sql);
        // // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':hashedCode', $codeHashed);
            $stmt->bindparam(':createdBy', $createdBy);
            $stmt->bindparam(':supID', $supportID);
            $stmt->bindparam(':perID', $permissionsID);
            $stmt->execute();
            $result = $stmt->execute();
        }

        $stmt = null;
        $db = null;

        $ModelResponse =  array(
            "success" => true,
            "data" => $result
        );

        return $ModelResponse;
    } catch (\PDOException $e) {

        $ModelResponse =  array(
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}


public function insertCode($supportID,$createdBy,$permissionsID,$codeHashed)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        $result="";
        $canChanged = $permissionsID==3?1:0;
        // CREATE query
        $sql = "INSERT INTO override_codes 
        (permissions_owner_id, permissions_id, override_code, owner_can_change, is_void, created_on, updated_on, created_by) 
        VALUES (:supID, :perID, :hashedCode, :canChange, '0', now(), NULL, :createdBy);";

        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':supID', $supportID);
            $stmt->bindparam(':perID', $permissionsID);
            $stmt->bindparam(':hashedCode', $codeHashed);
            $stmt->bindparam(':canChange', $canChanged);
            $stmt->bindparam(':createdBy', $createdBy);
            $result = $stmt->execute();
        }

        $stmt = null;
        $db = null;

        $ModelResponse =  array(
            "success" => true,
            "data" => $result
        );

        return $ModelResponse;
    } catch (\PDOException $e) {

        $ModelResponse =  array(
            "success" => false,
            "data" => $e->getMessage()
        );

        return $ModelResponse;
    }
}





public function notifySupervisor($managerID, $supID)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $result = "";

            date_default_timezone_set('Asia/Manila');
            // $now = time();
            // $now = strtotime("now");

            // $date = new DateTime();
            // $date->format('M j, Y g:i A');

            // $discount_start_date = '03/27/2012 18:47'; 
            // $start_date = date('Y-m-d H:i:s', strtotime($discount_start_date));   
            $now = date('Y-m-d H:i:s', strtotime("now")); 

            $sysgen = 'MANAGER #'.$managerID.' PERFORMED APPROVAL CODE RESET-GENERATE FOR SUP #'.$supID.' ON '.$now ;

            $sql="INSERT INTO `support_notifications` 
            (`id`, `ticket_actions_id`, `recipient_id`, 
            `support_ticket_id`, `notification_type_id`, 
            `generated_by`, `permissions_id`, `permissions_owner`, 
            `system_generated_description`, `has_taken_action`, `is_deleted`, 
            `is_read`, `created_on`) 
            VALUES (NULL, NULL, :recipientID, 
            NULL, '5', 
            :createdBy, '1', :permissionsOwner, 
            :sysgen, '0', '0', 
            '0', current_timestamp());";

            // Prepare statement
            $stmt =  $conn->prepare($sql); 
    
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':recipientID', $supID);
                $stmt->bindparam(':createdBy', $managerID);
                $stmt->bindparam(':permissionsOwner', $supID);
                $stmt->bindparam(':sysgen', $sysgen);
                $result = $stmt->execute();
            }
    
            $conn=null;
            $stmt = null;
            $db = null;

            $ModelResponse =  array(
                "success" => true,
                "data" => $result
            );

            return $ModelResponse;
        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );
        }}






















    // public function template()
    // {
    //     try {

    //         $db = new DB();
    //         $conn = $db->connect();


    //         $stmt = null;
    //         $db = null;

    //         $ModelResponse =  array(
    //             "success" => true,
    //             "data" => $result
    //         );

    //         return $ModelResponse;
    //     } catch (\PDOException $e) {

    //         $ModelResponse =  array(
    //             "success" => false,
    //             "data" => $e->getMessage()
    //         );

    //         return $ModelResponse;
    //     }
    // }

}