<?php

namespace App\Models;

use App\Db\DB;
use PDO;
use PDOException;

class User 
{
    // DB stuff
    private $table = 'hh_user';

    // Constructor 
    public function __construct(){
    }

    // @name    Check Phone Number
    // @params  user's phone number
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data vlaue is true if found in database and false if not in database
    public function is_in_db($phone_number){
        try{

            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT * FROM ".$this->table." WHERE phone_no = :phone";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':phone', $phone_number);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$result ? true : false
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

    // @name    Adds a user to the database
    // @params  phone number, first name, last name
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is
    public function createUser($first_name, $last_name, $phone_number, $password){

        // Create Password Hash
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "INSERT INTO ".$this->table."(user_type_id, first_name, last_name, phone_no, password) 
                        values(:utypeid,:fname,:lname,:phone,:pass)";
            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $utypeID = 1;
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':utypeid', $utypeID);
                $stmt->bindparam(':fname', $first_name);
                $stmt->bindparam(':lname', $last_name);
                $stmt->bindparam(':phone', $phone_number);
                $stmt->bindparam(':pass', $hashed_pass);
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

    

    // @name    Gets All Users in the Database
    // @params  limit number, default is null and thus returns all users
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data vlaue is is a PHP array of user objects, empty array when no data match in database
    public function getAll($limit = null){
        try{
            $db = new DB();
            $conn = $db->connect();

            $sql = "";

            // CREATE query
            if($limit){
                $sql = "SELECT * FROM ".$this->table." LIMIT $limit";
            } else {
                $sql = "SELECT * FROM ".$this->table;
            }

            
            // query statement
            $stmt =  $conn->query($sql);

            // check if statement is successfil
            if($stmt){
                $result = $stmt->fetchAll();
            }

            $conn=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$result,
            );

            return  $ModelResponse;

        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );

            return $ModelResponse;
        }
    }

    // @name    Connects a user with homeowner attributes
    // @params  id
    // @returns true on a successful add or PDOException/false if error
    private function createHomeowner($phone){
        return $result == false ? false : true;
    }



    // @name    Delete user by ID
    // @params  id
    // @returns true on a successful add or PDOException/false if error
    public function delteUserByID($phone){
        return $result == false ? false : true;
    }


// This function will be depreciated soon (Homeowner is still using this for validation),
// But once homeowner is updated to the new registration process, the getUserAccountsByPhone will be used instead
    // @name    Gets a user from database by phone number
    // @params  phone
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data vlaue is is a user data object
    public function getUserByPhone($phone){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT * FROM ".$this->table." WHERE phone_no=:phone";

            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':phone', $phone);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$result == false ? false : $result
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


    // @name    Gets all users associated with a phone number
    // @params  phone
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data vlaue is is a user data object
    // @referencedBy userPhoneCheck Controller
    public function getUserAccountsByPhone($phone){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT * FROM ".$this->table." WHERE phone_no=:phone";

            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':phone', $phone);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            $stmt=null;
            $db=null;

            $ModelResponse =  array(
                "success"=>true,
                "data"=>$result == false ? false : $result
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



    // @name    Gets a user from database by ID
    // @params  id
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data vlaue is is a user data object
    public function getUserByID($id){
        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $sql = "SELECT * FROM ".$this->table." WHERE user_id=:id";

            
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':id', $id);
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

}