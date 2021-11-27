<?php

namespace App\Models;

use App\Db\DB;
use PDO;
use PDOException;

class SupportTicket 
{
        // DB stuff
        private $table = 'support_ticket';

        // Constructor 
        public function __construct(){
        }

        // @name    Adds a support ticket to the database
        // @params  
        // @returns a Model Response object with the attributes "success" and "data"
        //          sucess value is true when PDO is successful and false on failure
        //          data value is
        public function createTicket($author, $subcategory, $authorDescription, $totalImages){
            try{
                $db = new DB();
                $conn = $db->connect();
    
                // CREATE query
                $sql = "INSERT INTO ".$this->table."(author, subcategory, authorDescription, numberOfImages) 
                            values(:author,:subcat,:desc,:totalImages)";
                
                // Prepare statement
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':author', $author);
                    $stmt->bindparam(':subcat', $subcategory);
                    $stmt->bindparam(':desc', $authorDescription);
                    $stmt->bindparam(':totalImages',  $totalImages);
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

<<<<<<< HEAD
        // @name    Retrieves all resolved tickets from the database
        // @params  
        // @returns a Model Response object containing the requested records
        public function getResolved($id = null){

            /*
                To be added: (1) Verify if account type is support agent
                             (2) Return "none" if no result
            */
=======

        // @name    gets all tickets from the database
        // @params  none
        // @returns a Model Response object with the attributes "success" and "data"
        //          sucess value is true when PDO is successful and false on failure
        //          data value is
        public function get_All($id = null){
>>>>>>> master

            try{
                $db = new DB();
                $conn = $db->connect();
    
<<<<<<< HEAD
                if($id == null){
                    $sql = "SELECT * FROM ".$this->table." WHERE status=3";
                    $stmt = $conn->query($sql);
                    $result = $stmt->fetchAll();
                } else {
                    $sql = "SELECT * FROM ".$this->table." WHERE status=3 AND assigned_agent=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindparam(':id', $id);
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                }
               
                $stmt=null;
                $db=null;
=======
                // CREATE query
                $sql = "";

                if($id == null){
                    $sql = "SELECT * FROM ".$this->table;
                    // query statement
                    $stmt =  $conn->query($sql);

                    // check if statement is successfil
                    if($stmt){
                        $result = $stmt->fetchAll();
                    }

                } else {
                    $sql = "SELECT * FROM ".$this->table." WHERE assigned_agent = :id";
                    // Prepare statement
                    $stmt =  $conn->prepare($sql);
                    $result = "";

                    // Only fetch if prepare succeeded
                    if ($stmt !== false) {
                        $stmt->bindparam(':id', $id);
                        $stmt->execute();
                        $result = $stmt->fetchAll();
                    }

                    $stmt=null;
                    $db=null;
                }
                
                
                $conn=null;
                $db=null;

>>>>>>> master
    
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
<<<<<<< HEAD
        }
=======

        }










































































































>>>>>>> master
}