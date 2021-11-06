<?php

namespace App\Models;

use App\Db\DB;
use PDO;
use PDOException;

class User 
{

    // DB stuff
    private $conn;
    private $table = 'hh_user';

    // Properties
    private $user_id;
    private $user_type;
    private $user_status;
    private $first_name;
    private $last_name;
    private $phone_number;
    private $date_time_created;
    private $messenger_status;
    private $timestamp_last_active;

    // Constructor 
    public function __construct(){
    }

    // @name    Check Phone Number
    // @params  user's phone number
    // @returns true if number is in db or false on failure/number is not in db.
    public function is_in_db($phone_number){
        try{

            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $query = "SELECT * FROM ".$this->table." WHERE phone_no = :phone";
            
            // Prepare statement
            $stmt =  $conn->prepare($query);
            
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':phone', $phone_number);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
            $stmt=null;
            $db=null;

            return $result == false ? false : true;

        } catch (\PDOException $e) {

            return $e->getMessage();
        }
    }

    // @name    Adds a user to the database
    // @params  phone number, first name, last name
    // @returns true on a successful add or PDOException/false if error
    public function register($first_name, $last_name, $phone_number, $password){

        // Create Password Hash
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

        try{
            $db = new DB();
            $conn = $db->connect();

            // CREATE query
            $query = "INSERT INTO hh_user(user_type_id, first_name, last_name, phone_no, password) 
                        values(:utypeid,:fname,:lname,:phone,:pass)";
            
            // Prepare statement
            $stmt =  $conn->prepare($query);
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

            return $result == false ? false : true;

        } catch (\PDOException $e) {

            return $e->getMessage();
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



    // @name    Gets a user from database by phone number
    // @params  id
    // @returns true on a successful add or PDOException/false if error
    public function getUserByPhone($phone){
        return $result == false ? false : true;
    }



    // @name    Gets a user from database by ID
    // @params  id
    // @returns true on a successful add object or PDOException/false if error
    public function getUserByID($id){
        return $result == false ? false : true;
    }

}