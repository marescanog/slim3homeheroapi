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
        public function createTicket($author, $subcategory, $authorDescription, $systemDescription, $totalImages = 0){
            try{
                $db = new DB();
                $conn = $db->connect();
    
                // CREATE query
                $sql = " SET @@session.time_zone = '+08:00'; 
                         BEGIN;
                            INSERT INTO ".$this->table."(author, issue_id, system_Description, author_Description, numberOfImages) 
                                values(:author,:issueID, :sysDesc, :authDesc, :totalImages);

                            INSERT INTO ticket_actions (action_taken, system_generated_description, support_ticket)
                                VALUES(1, :sysDesc, LAST_INSERT_ID());

                            COMMIT;
                        ";
                //Create also an action in the table
                // Prepare statement
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':author', $author);
                    $stmt->bindparam(':issueID', $subcategory);
                    $stmt->bindparam(':sysDesc', $authorDescription);
                    $stmt->bindparam(':authDesc', $systemDescription);
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


        // @name    gets all tickets from the database
        // @params  none
        // @returns a Model Response object with the attributes "success" and "data"
        //          sucess value is true when PDO is successful and false on failure
        //          data value is
        public function get_All($id = null){

            try{
                $db = new DB();
                $conn = $db->connect();
    
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


// ===================================================================
//  April 14 2022

// Get all NEW tickets, Get by support ID, Get by role
// @desc    gets all new tickets or counts all new tickets
//          LIMITS to 1000 tickets unless specified
// @params  optional role, optional id (with id/role pulls data role-specific and id-specific, without ID/Role Pulls/Counts All)
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
// Note, For pagination select count all
public function get_Tickets($status = 1, $count = true, $id = null, $role = null, $limit = 1000, $offset = 0){
    try{
        $db = new DB();
        $conn = $db->connect();

        // Registration/Verification, Customer Support, Technical Support
        $roleSubTypes = ["1,2,3","4,5,6,7,8,9","10,11,12,13,14,15,16"];

        // New, Ongoing, Resolved
        $statusTypes = ["status = 1","status = 2","status IN (3,4)","is_Escalated = 1"];

        // CREATE query
        $sql = "";
        $result = "";
        $sqlType = $count == true ? "COUNT(*)":"*";    
        $filterType = "";

        if($id != null){
            $filterType =" AND assigned_agent=:id";   
        } else if($role != null) {
            $filterType =" AND issue_id IN (".$roleSubTypes[$role-1].")";
        }

        // GET NEW TICKETS
        // GET NEW TICKETS -> SELECT COUNT(*) FROM `support_ticket` WHERE status = 1 AND is_Archived = 0 AND assigned_agent = 163;
        $sql = "SELECT ".$sqlType." FROM ".$this->table.' WHERE '.$statusTypes[$status-1].' AND is_Archived = 0'.$filterType.($count == true?";":(" LIMIT ".$limit." OFFSET ".$offset.";"));
        

        // BIND ANY RELEVANT PARAMETERS
        if($id != null){
            // check if statement is successful
                $stmt =  $conn->prepare($sql);
            if($stmt != false){
                $stmt->bindparam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }  
        } else {
            // query statement
            $stmt =  $conn->query($sql);
            // check if statement is successfil
            if($stmt){
                $result = $stmt->fetchAll();
            }
        } 
        
        $conn=null;
        $db=null;

        $ModelResponse =  array(
            "success"=>true,
            "data"=>$result
        );

        return $ModelResponse;

    } catch (\PDOException $e) {

        $ModelResponse =  array(
            "success"=>false,
            "data"=>"Error: ".$e->getMessage()
        );

        return $ModelResponse;
    }

}


// @name    gets all transferred tickets from the database based on agent ID
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_transferred_tickets($count = false,$id = null,$limit=1000,$offset=0){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sqlType = $count == true ? "COUNT(*)":"*"; 

        // if($id == null){
        //     $sql = "SELECT * FROM ".$this->table;
        //     // query statement
        //     $stmt =  $conn->query($sql);

        //     // check if statement is successfil
        //     if($stmt){
        //         $result = $stmt->fetchAll();
        //     }

        // } else {
            $sql = "SELECT ".$sqlType." FROM ".$this->table." st RIGHT JOIN ticket_assignment ta ON st.id = ta.support_ticket WHERE ta.previous_agent = :id GROUP BY ta.support_ticket ".($count == true?";":(" LIMIT ".$limit." OFFSET ".$offset.";"));
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
        // }
        
        
        $conn=null;
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