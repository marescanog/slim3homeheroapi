<?php

namespace App\Models;

use App\Db\DB;
use PDO;
use PDOException;

class GenerateData 
{
        // DB stuff
        private $table = 'homeowner';

        // Constructor 
        public function __construct(){
        }

        public function getLastMobileNumber(){
            try{
                $db = new DB();
                $conn = $db->connect();
                $sql = "SELECT hh.phone_no FROM `hh_user` hh ORDER BY hh.user_id DESC LIMIT 1;";
                // Prepare statement
                $stmt =  $conn->prepare($sql);
                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $result = $stmt->execute();
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
                $stmt=null;
                $db=null;
                $ModelResponse =  array(
                    "success"=>true,
                    "data"=>$result
                );
                return $ModelResponse;
            }catch(\PDOException $e){
                $ModelResponse =  array(
                    "success"=>false,
                    "data"=>$e->getMessage()
                );
                return $ModelResponse;
            }
        }

        public function getUserType($id){
            try{
                $db = new DB();
                $conn = $db->connect();
                $sql = "SELECT hh.user_type_id FROM `hh_user` hh WHERE hh.user_id = :userID;";
                // Prepare statement
                $stmt =  $conn->prepare($sql);
                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':userID', $id);
                    $result = $stmt->execute();
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

        public function gethomeownersID($numberOfUsers){

            // Create Password Hash
            // $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    
            try{
                if(!is_numeric($numberOfUsers)){
                    return   array(
                        "success"=>false,
                        "data"=>"Not numeric"
                    );
                }
                $db = new DB();
                $conn = $db->connect();
    
                // CREATE query
                $sql = "SELECT user_id FROM `hh_user` hh WHERE hh.user_type_id = 1 ORDER BY user_id DESC LIMIT ".$numberOfUsers.";";
                
                // Prepare statement
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    // $stmt->bindparam(':numberOfUsers', $numberOfUsers);
                    $result = $stmt->execute();
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



        public function getWorkersID($numberOfUsers, $isRegistered = false){

            // SELECT * FROM `hh_user` hh 
            // LEFT JOIN worker w ON w.id = hh.user_id
            // WHERE hh.user_type_id = 2 
            // AND w.has_completed_registration = 0
            // ORDER BY `hh`.`user_id` DESC LIMIT 10
    
            try{
                if(!is_numeric($numberOfUsers)){
                    return   array(
                        "success"=>false,
                        "data"=>"Not numeric"
                    );
                }
                $db = new DB();
                $conn = $db->connect();

                // CREATE query
                $sql = "SELECT hh.user_id FROM `hh_user` hh 
                LEFT JOIN worker w ON w.id = hh.user_id
                WHERE hh.user_type_id = 2 ";

                if($isRegistered == false){
                    $sql =   $sql."AND w.has_completed_registration = 0 ";
                }

                $sql =   $sql." ORDER BY `hh`.`user_id` DESC LIMIT ".$numberOfUsers.";";    
                
                // Prepare statement
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    // $stmt->bindparam(':numberOfUsers', $numberOfUsers);
                    $result = $stmt->execute();
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






        public function getUsersID($numberOfUsers){

            // Create Password Hash
            // $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    
            try{
                if(!is_numeric($numberOfUsers)){
                    return   array(
                        "success"=>false,
                        "data"=>"Not numeric"
                    );
                }
                $db = new DB();
                $conn = $db->connect();
    
                // CREATE query
                $sql = "SELECT user_id, first_name, last_name FROM `hh_user` ORDER BY user_id DESC LIMIT ".$numberOfUsers.";";
                
                // Prepare statement
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    // $stmt->bindparam(':numberOfUsers', $numberOfUsers);
                    $result = $stmt->execute();
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

        public function getbarangayspercity($city){
   
            try{
                if(!is_numeric($city)){
                    return   array(
                        "success"=>false,
                        "data"=>"Not numeric"
                    );
                }
                $db = new DB();
                $conn = $db->connect();
    
                // CREATE query
                $sql = "SELECT b.id FROM `barangay` b WHERE b.city_id = :city;";
                
                // Prepare statement
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':city', $city);
                    $result = $stmt->execute();
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

        public function getSupportEmail($email){
        
            try{
                $db = new DB();
                $conn = $db->connect();
                $sql = "SELECT * FROM support_agent sa WHERE sa.email = :email;";
    
                // Prepare statement
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':email', $email);
                    $result = $stmt->execute();
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













    public function updateHomeownerCreateDate($id, $date, $userType){
        
        try{
            $db = new DB();
            $conn = $db->connect();
            $sql = "";

            // CREATE query
            switch($userType){
                case 1:
                    $sql = "BEGIN;
                            UPDATE `hh_user` SET `created_on` = :date WHERE `hh_user`.`user_id` = :userID;
                            UPDATE `homeowner` SET `created_on` = :date2 WHERE `homeowner`.`id` = :userID2;
                            COMMIT;";
                    break;
                case 2:
                    $sql = "BEGIN;
                            UPDATE `hh_user` SET `created_on` = :date WHERE `hh_user`.`user_id` = :userID;
                            UPDATE `worker` SET `created_on` = :date2 WHERE `worker`.`id` = :userID2;
                            COMMIT;";
                    break;
                case 3:
                case 4:
                    return  array(
                        "success"=>false,
                        "data"=>"Not coded yet"
                    );
                    // $sql = "BEGIN;
                    //         UPDATE `hh_user` SET `created_on` = '2020-06-09 15:29:40' WHERE `hh_user`.`user_id` = 699;
                    //         UPDATE `homeowner` SET `created_on` = '2020-06-09 15:29:40' WHERE `hh_user`.`id` = 699;
                    //         COMMIT;
                    //         ";
                    break;
                default:
                    return  array(
                        "success"=>false,
                        "data"=>"Not in list of types"
                    );
            }
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $utypeID = 1;
            // Only fetch if prepare succeeded //$id, $date,
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $id);
                $stmt->bindparam(':date', $date);
                $stmt->bindparam(':userID2', $id);
                $stmt->bindparam(':date2', $date);
                $result = $stmt->execute();
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>   $result
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


    public function getUserCreationDate($userID){
        
        try{
            $db = new DB();
            $conn = $db->connect();
            $sql = "SELECT hh.created_on FROM `hh_user` hh WHERE hh.user_id = :userID;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

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


    public function updateSupportTicketDate($userID, $date){
        try{
            $db = new DB();
            $conn = $db->connect();
            $sql = "UPDATE support_ticket st SET st.created_on = :dateye 
            WHERE st.author = :userID AND issue_id = 1;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded //$id, $date,
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->bindparam(':dateye', $date);
                $result = $stmt->execute();
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$sql
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

    public function createSupport($userID, $role, $date, $email, $supID = null){
        
        try{
            if(!is_numeric($role)){
                return   array(
                    "success"=>false,
                    "data"=>"Not numeric"
                );
            }

            $db = new DB();
            $conn = $db->connect();

            $userType = ($role == 6 || $role == 5) ? 4 : 3;

            $sql = "BEGIN;
            DELETE FROM `homeowner` WHERE `homeowner`.`id` = :userID;
            UPDATE `hh_user` SET `user_type_id` = :userType, `created_on` = :cdate WHERE `hh_user`.`user_id` = :userID2;
            ";

            if($supID != null){
                $sql = $sql."INSERT INTO `support_agent` (`id`, `email`, `role_type`, `supervisor_id`, `is_deleted`, 
                `created_on`) VALUES (:userID4, :email, :roleType, :supID, '0', :cdate2);";
                
                $sql = $sql."INSERT INTO `sup_assignments` (`id`, `sup_id`, `agent_id`, `assigned_on`) VALUES (NULL, :supID2, :userID5, :cdate3);
                ";
            } else {
                $sql = $sql."INSERT INTO `support_agent` (`id`, `email`, `role_type`, `supervisor_id`, `is_deleted`, 
                `created_on`) VALUES (:userID6, :email, :roleType, null, '0', :cdate4);";
            }

            $sql =   $sql."COMMIT;";

            // // Prepare statement
            $stmt =  $conn->prepare($sql);

            // // Only fetch if prepare succeeded //$id, $date,
            if ($stmt !== false) {
                $stmt->bindparam(':userID', $userID);
                $stmt->bindparam(':userType', $userType);
                $stmt->bindparam(':userID2', $userID);
                $stmt->bindparam(':cdate', $date);

                $stmt->bindparam(':email', $email);
                $stmt->bindparam(':roleType', $role);
                if($supID != null){
                    $stmt->bindparam(':userID4', $userID);
                    $stmt->bindparam(':supID',$supID);
                    $stmt->bindparam(':cdate2', $date);
                    $stmt->bindparam(':supID2',$supID);
                    $stmt->bindparam(':userID5', $userID);
                    $stmt->bindparam(':cdate3', $date);
                } else {
                    $stmt->bindparam(':userID6', $userID);
                    $stmt->bindparam(':cdate4', $date);
                }
                
                $result = $stmt->execute();
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                // "data"=>$sql,
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











































    public function test($id, $date, $userType){
        
        try{
            $db = new DB();
            $conn = $db->connect();
            $sql = "";

            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$sql
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