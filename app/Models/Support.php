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


    // For Get Codes
    public function managerIsGettingSupervisorCodes(){
        try {

            $db = new DB();
            $conn = $db->connect();

            $result = [];

            $sql = "SELECT sa.id as sup_id, CONCAT(hh.last_name, ', ', hh.first_name) as full_name, hh.first_name, hh.last_name,
            oc.permissions_owner_id, oc.permissions_id, oc.override_code, oc.owner_can_change, oc.is_void
            FROM support_agent sa
            LEFT JOIN override_codes oc ON sa.id = oc.permissions_owner_id
            LEFT JOIN hh_user hh ON sa.id = hh.user_id
            WHERE sa.role_type = 4
            AND sa.is_deleted = 0";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
        }
    }


    public function manager_getList_of_supervisors($isActive = false)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $result = [];

            $sql ="SELECT sa.id as sup_id, sa.email,
            CONCAT(hh.last_name, ', ', hh.first_name) as full_name, hh.first_name, hh.last_name 
            FROM support_agent sa 
            LEFT JOIN hh_user hh ON sa.id = hh.user_id
            WHERE sa.role_type = 4
            AND sa.is_deleted = 0;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            $stmt = null;
            $db = null;
            $conn = null;

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



    // ======================================================================================
    // ======================================================================================
    // May 24, 2022



    public function getAccBaseInfo($userID)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $result = "";

            $sql ="SELECT sa.id, sa.email, sa.is_deleted, sa.supervisor_id, sa.created_on as date_joined,
            hha.last_name as acc_lname, hha.first_name acc_fname, CONCAT(hha.last_name,', ', hha.first_name) as acc_full_name,
            hhs.last_name as sup_lname, hhs.first_name sup_fname, CONCAT(hhs.last_name,', ', hhs.first_name) as sup_full_name
            FROM support_agent sa 
            LEFT JOIN hh_user hha ON sa.id = hha.user_id
            LEFT JOIN hh_user hhs ON sa.supervisor_id = hhs.user_id
            WHERE sa.id = :userID;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            $stmt = null;
            $db = null;
            $conn = null;

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


        public function get_agent_acc_stats($userID)
        {
            try {
    
                $db = new DB();
                $conn = $db->connect();
    
                $result = "";
    
                // $sql ="SELECT COUNT(*) as `value`, sts.status as `key`
                //     FROM support_ticket st
                //     LEFT JOIN support_ticket_status sts ON st.status = sts.id
                //     WHERE st.assigned_agent = :userID
                //     GROUP BY st.status;";

                $sql = "SELECT COUNT(at.id) as `value`, sts.status as `key`
                FROM support_ticket_status sts
                LEFT JOIN 
                (SELECT * FROM support_ticket st WHERE st.assigned_agent = :userID) as at ON sts.id =  at.status 
                GROUP BY sts.id;";
    
                // Prepare statement
                $stmt =  $conn->prepare($sql);
    
                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':userID', $userID);
                    $stmt->execute();
                    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
    
                $stmt = null;
                $db = null;
                $conn = null;
    
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






            public function get_supervisor_acc_stats($userID)
            {
                try {
        
                    $db = new DB();
                    $conn = $db->connect();
        
                    $result = "";
        
                    $sql ="SELECT COUNT(agents.id) as `value`, sat.role as `key` 
                    FROM support_agent_role_type sat
                    LEFT JOIN (SELECT * FROM support_agent sa WHERE sa.supervisor_id = :userID) as agents ON sat.id = agents.role_type
                    GROUP BY sat.id;";
        
                    // Prepare statement
                    $stmt =  $conn->prepare($sql);
        
                    // Only fetch if prepare succeeded
                    if ($stmt !== false) {
                        $stmt->bindparam(':userID', $userID);
                        $stmt->execute();
                        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    }
        
                    $stmt = null;
                    $db = null;
                    $conn = null;
        
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







                public function get_manager_acc_stats()
                {
                    try {
            
                        $db = new DB();
                        $conn = $db->connect();
            
                        $result = "";
            
                        $sql ="SELECT COUNT(sa.id) as `value`, r.role as `key` FROM support_agent_role_type r 
                        LEFT JOIN support_agent sa 
                        ON r.id = sa.role_type
                        GROUP BY r.id;";
            
                        // Prepare statement
                        $stmt =  $conn->prepare($sql);
            
                        // Only fetch if prepare succeeded
                        if ($stmt !== false) {
                            $stmt->execute();
                            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                        }
            
                        $stmt = null;
                        $db = null;
                        $conn = null;
            
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
        // }}
}