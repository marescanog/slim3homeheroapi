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

    // ============================================================================================================
    // @desc    This function builds a query to insert or update skill values in the database
    // @params  $skills_toDelete (array of skill IDS), $skills_toAdd (array of skill IDS), $current_sql(string), $userID (INT),
    // @returns an SQL statement which updates an existing skill or inserts a new skill
    private function skills_QueryBuilder($skills_toAdd, $skills_toDelete, $skills_toUpdate, $current_sql, $userID){
        $addSkillsSql = "";
        $deleteSkillsSql = "";
        $updatedSkillsSql = "";

        if(count($skills_toAdd) !== 0){
            $checkSkillSql = "SELECT * FROM `skillset` WHERE worker_id = $userID AND skill = ";
            $addSkillsSql = "INSERT INTO `skillset` (`worker_id`, `skill`) VALUES ";
            for($x = 0; $x < count($skills_toAdd); $x++){
                $addSkillsSql = $addSkillsSql."($userID,".$skills_toAdd[$x].")";
                $addSkillsSql = $x ==  count($skills_toAdd)-1 ?  $addSkillsSql.";" : $addSkillsSql.", ";
            }
        }

        if(count($skills_toDelete) !== 0){
            $baseDelete = "UPDATE `skillset` SET is_deleted = 1 WHERE worker_id = $userID AND skill = ";
            for($x = 0; $x < count($skills_toDelete); $x++){
                $deleteSkillsSql = $deleteSkillsSql.$baseDelete.$skills_toDelete[$x].";";
            }
        }

        if(count($skills_toUpdate) !== 0){
            $baseUpdate = "UPDATE `skillset` SET is_deleted = 0 WHERE worker_id = $userID AND skill = ";
            for($x = 0; $x < count($skills_toUpdate); $x++){
                $updatedSkillsSql = $updatedSkillsSql.$baseUpdate.$skills_toUpdate[$x].";";
            }
        }

        return $current_sql.$addSkillsSql.$deleteSkillsSql.$updatedSkillsSql;
    }

    // ============================================================================================================
    // @desc    Saves worker personal info for registration
    // @params  $userID, $skills_toDelete (array of skill IDS), $skills_toAdd (array of skill IDS)
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is 
    public function save_personalInformation(
                $userID, $skill_data, $default_rate, $default_rate_type, $clearance_no, $expiration_date,
                $file_id = true ,  $file_name = null ,   $file_path = null ,  $file_type = null 
        ){
        try{
            // track the necessary parameters to bind PDO data
            $bind_ClearanceNo = false;
            $bind_fileName = false;
            $bind_filePath = false;
            $bind_fileType = false;
            $bind_NBI_id = false;
            $bind_file_id = false;

            // Build Query based on parameters
            $baseSql = "SET @@session.time_zone = '+08:00'; BEGIN;";
            // Add skills query
            $full_sql = $this->skills_QueryBuilder($skill_data["skills_toAdd"], $skill_data["skills_toDelete"], $skill_data["skills_toUpdate"],  $baseSql, ':userID');
            // Construct query for rate and type
            $sql_update_defaultRate = "UPDATE `worker` SET default_rate = :defaultRate WHERE id = :userID;";
            $sql_update_defaultType = "UPDATE `worker` SET default_rate_type = :defaultRateType WHERE id = :userID;";
            // Add rate and type query
            $full_sql = $full_sql.$sql_update_defaultRate.$sql_update_defaultType;

            // Check if user already has nbi_file information existing
            $hasNBI = $this->get_nbi_information($userID);
            if($hasNBI['success'] == false){
                $ModelResponse =  array(
                    "success"=>false,
                    "data"=>$hasNBI['data']
                );
                return $ModelResponse;
            }

            // Construct query for nbi info
            $sql_insert_NBI_info = "INSERT into NBI_information (clearance_no, worker_id, expiration_date) VALUES (:clearanceNo, :userID, :expiration_date);";
            $sql_insert_NBI_file_and_info_junction = "INSERT INTO `NBI_files` (`NBI_id`, `file_id`) VALUES (@nbiInfoNumber, @nbiFileNumber);";
            $sql_insert_files = "INSERT into `file` (`file_name`,`file_type`,`file_path`) VALUES (:nfileName, :nfileType, :nfilePath);SET @nbiFileNumber:=LAST_INSERT_ID();";

            if($hasNBI['data'] == false){
                // Insert new NBI info when none found
                $full_sql = $full_sql.$sql_insert_NBI_info. "SET @nbiInfoNumber:=LAST_INSERT_ID();";

                // Add new file
                
                $full_sql = $full_sql. $sql_insert_files;

                // Link new entries into the junction table
                $full_sql = $full_sql.$sql_insert_NBI_file_and_info_junction;

                $bind_ClearanceNo = true;
                $bind_fileName = true;
                $bind_filePath = true;
                $bind_fileType = true;

            } else {
                // Update existing NBI info
                // Check if the clearance number is the same as the one we will insert
                if($hasNBI['data']['clearance_no'] !== $clearance_no){
                    
                    // add new NBI info entry
                    $full_sql = $full_sql.$sql_insert_NBI_info;

                    // file_id false means it is a new file, thus there is no id for it yet
                    if($file_id == 'false'){

                        // add new file entry
                        $full_sql = $full_sql. "SET @nbiInfoNumber:=LAST_INSERT_ID();" .$sql_insert_files;

                        // Link new entries into the junction table
                        $full_sql = $full_sql.$sql_insert_NBI_file_and_info_junction;

                        $bind_fileName = true;
                        $bind_filePath = true;
                        $bind_fileType = true;

                    } else {
                        // insert new junction with old file id
                        $full_sql = $full_sql. "SET @nbiInfoNumber:=LAST_INSERT_ID();";

                        $sql_insert_NBI_file_and_info_junction_old_id = "INSERT INTO `NBI_files` (`NBI_id`, `file_id`) VALUES (@nbiInfoNumber, :nfileID);";
                     
                        // Link new NBI info entry and old file enty into the junction table
                        $full_sql = $full_sql.$sql_insert_NBI_file_and_info_junction_old_id;

                        $bind_file_id = true;
                    }

                    $bind_ClearanceNo = true;

                } else {
                    // update old NBI entry
                    $sql_update_NBI_info_date = "UPDATE `NBI_information` SET expiration_date = :expiration_date WHERE id = :NBI_id;";
                    $full_sql = $full_sql.$sql_update_NBI_info_date;
                    $bind_NBI_id = true;

                    // insert check for  $file_id -> Change
                    // <code to process if has new file, if it has, add a new file to db. Otherwise don't. grab value for last insert IF>
                    // <mark last file as deleted>
                }
            }

            // End query builder / transaction
            $full_sql = $full_sql."COMMIT;";

            // // -------------
            // // Now that we have a generated SQL statement, we apply it to the DB
            // // Create new DB connection
            // $db = new DB();
            // $conn = $db->connect();

            // // Prepare statement
            // $stmt =  $conn->prepare($sql);

            // // Only fetch if prepare succeeded
            // if ($stmt !== false) {
            //     $stmt->bindparam(':userID', $userID);
            //     $stmt->bindparam(':defaultRate', $default_rate);
            //     $stmt->bindparam(':defaultRateType', $default_rate_type);
            //     $stmt->bindparam(':expiration_date', $expiration_date);
            //     if($bind_NBI_id == true){
            //         $stmt->bindparam(':NBI_id', $expiration_date); 
            //     }
            //     if( $bind_ClearanceNo  == true){
            //         $stmt->bindparam(':clearanceNo', $clearance_no); 
            //     }
            //     if( $bind_fileName == true){
            //         $stmt->bindparam(':nfileName', $file_name); 
            //     }
            //     if($bind_filePath == true){
            //         $stmt->bindparam(':nfilePath', $file_path); 
            //     }
            //     if($bind_fileType == true){
            //         $stmt->bindparam(':nfileType', $file_type); 
            //     }
            //     if($bind_file_id == true){
            //         $stmt->bindparam(':nfileID', $file_id); 
            //     }
            //     $stmt->execute();
            // }
   
            // $stmt=null;
            // $db=null;

            // $ModelResponse =  array(
            //     "success"=>true,
            //     "data"=>$result
            // );
            // return $ModelResponse;

            $checkData = array(
                "statement" => $full_sql,
                "bind_ClearanceNo" => $bind_ClearanceNo,
                "bind_fileName" => $bind_fileName,
                "bind_filePath" => $bind_filePath,
                "bind_fileType" => $bind_fileType,
                "bind_NBI_id" => $bind_NBI_id,
                "bind_file_id" => $bind_file_id
            );
            return  $checkData;


            //return $full_sql;

            //return $hasNBI['data']['id'];

        }catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
            
        }catch (\Exception $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }

    // ============================================================================================================
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

    // ============================================================================================================
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

    // ============================================================================================================
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

    // ============================================================================================================
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

    // ============================================================================================================
    // @desc    Retreives from the database a list of expertise the user has, 
    //          note: Expertise is different from the sub category project_type
    // @params  userID
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is an assocarray of expertise (IDs, ex [{"id"=>4,"name"=>"gardening"},{"id"=>1,"name"=>"carpentry"}]), empty array if empty
    public function getList_expertise($userID, $includeDeleted = false){
        try{

            $db = new DB();
            $conn = $db->connect();

            $sql = "";

            // CREATE query
            if($includeDeleted == true){
                $sql = "SELECT p.expertise as id, e.expertise as name 
                FROM skillset s
                JOIN project_type p ON s.skill = p.id 
                JOIN expertise e ON e.id = p.expertise 
                WHERE s.worker_id = :userID 
                GROUP BY e.id;";
            } else {
                $sql = "SELECT p.expertise as id, e.expertise as name 
                FROM skillset s
                JOIN project_type p ON s.skill = p.id 
                JOIN expertise e ON e.id = p.expertise 
                WHERE s.worker_id = :userID 
                AND s.is_deleted = 0
                AND p.is_deleted = 0
                AND e.is_deleted = 0
                GROUP BY e.id;";
            }

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

    
    // ============================================================================================================
    // @desc    Adds a user and a worker to the database
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

    // ============================================================================================================
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