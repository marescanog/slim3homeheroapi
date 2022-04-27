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
        $statusTypes = ["st.status = 1","st.status = 2","st.status IN (3,4)","st.is_Escalated = 1"];

        // CREATE query
        $sql = "";
        $result = "";
        $sqlType = $count == true ? "COUNT(*)":"st.id,st.author,st.issue_id,st.status,st.is_Escalated,st.is_Archived,st.assigned_agent,st.created_on,st.last_updated_on,st.assigned_on,st.has_AuthorTakenAction,hu.first_name,hu.last_name";    
        $filterType = "";

        if($id != null){
            $filterType =" AND st.assigned_agent=:id";   
        } else if($role != null) {
            $filterType =" AND st.issue_id IN (".$roleSubTypes[$role-1].")";
        }

        // GET NEW TICKETS
        // GET NEW TICKETS -> SELECT COUNT(*) FROM `support_ticket` WHERE status = 1 AND is_Archived = 0 AND assigned_agent = 163;
        $sql = "SELECT ".$sqlType." FROM ".$this->table." st".($count == true?"":(" LEFT JOIN hh_user hu ON st.assigned_agent = hu.user_id ")).' WHERE '.$statusTypes[$status-1].' AND st.is_Archived = 0'.$filterType.($count == true?";":(" ORDER BY st.id DESC LIMIT ".$limit." OFFSET ".$offset.";"));
        

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

        $sqlType = $count == true ? "COUNT(*)":"st.id,st.author,st.issue_id,st.status,st.is_Escalated,st.is_Archived,st.assigned_agent,st.created_on,st.last_updated_on,st.assigned_on,ta.date_assigned,ta.date_assigned,ta.newly_assigned_agent,ta.previous_agent,ta.transfer_reason,ta.support_ticket,hu.first_name,hu.last_name"; 

        // if($id == null){
        //     $sql = "SELECT * FROM ".$this->table;
        //     // query statement
        //     $stmt =  $conn->query($sql);

        //     // check if statement is successfil
        //     if($stmt){
        //         $result = $stmt->fetchAll();
        //     }

        // } else {

            //"st.id,st.author,st.issue_id,st.status,st.is_Escalated,st.is_Archived,st.assigned_agent,st.created_on,st.last_updated_on,st.assigned_on,st.has_AuthorTakenAction,hu.first_name,hu.last_name"

            $sql = "SELECT ".$sqlType." FROM ".$this->table." st RIGHT JOIN ticket_assignment ta ON st.id = ta.support_ticket LEFT JOIN hh_user hu ON  ta.newly_assigned_agent = hu.user_id WHERE ta.previous_agent = :id GROUP BY ta.support_ticket ".($count == true?";":(" ORDER BY st.id DESC LIMIT ".$limit." OFFSET ".$offset.";"));
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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


// ===================================================================
//  April 18 2022


// ["general","id","name","author","category"]
public function search($userID = null, $id = null, $category = null, $agent_name = null, $author_name = null, $limit=10, $offset=0){
    try{

        // $c_subTypes = ["-","Worker Registration","Worker Certification","User Home Verification","Billing Dispute","Rating Dispute","Job Post Issue","Job Order Issue","Complaint on other Users behavior","General Complaint","Inquiry","App Guidance","App Issues","Account Issue","Password Issue","Messaging Issue","Database Issue"];
        // $c_issue_id = property_exists($n, 'issue_id') ? ($n->issue_id > 0 || is_numeric($n->issue_id) ? $n->issue_id :"-" ) : "-";                
        // $c_head = "GEN";
        // if($c_issue_id>=1&&$c_issue_id<=3){
        //     $c_head = "REG";
        // }
        // if($c_issue_id>=4&&$c_issue_id<=9){
        //     $c_head = "DIS";
        // }
        // if($c_issue_id>=12&&$c_issue_id<=16){
        //     $c_head = "TEC";
        // }   

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";
        $result = ""; 
        
        // Search by ticket ID 
        if($id != null) {
            // JOIN ONLY first_name and last name of hhuser since id simple search
            $sql = "SELECT DISTINCT st.id,st.author,st.issue_id,st.status,st.is_Escalated,st.is_Archived,
            st.assigned_agent,st.created_on,st.last_updated_on,st.assigned_on,st.has_AuthorTakenAction,
            hu.first_name,hu.last_name, 
            hu2.first_name as author_first_name,hu2.last_name as author_last_name
            FROM `support_ticket` st 
            LEFT JOIN hh_user hu ON  st.assigned_agent = hu.user_id
            LEFT JOIN hh_user hu2 ON  st.author = hu2.user_id 
            WHERE st.id = :id
            GROUP BY st.id
            ORDER BY id 
            DESC LIMIT ".$limit." OFFSET ".$offset.";"; //limit & offset variables are already cleaned before passed. Default is 10 & 0 respectively
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        }
        
        // // Search by other categories
        // if($id == null){
        //     // DO AN OUTER JOIN INSTEAD
        //     // Also join categories?
        //     $sql = "SELECT * FROM ".$this->table." ORDER BY id DESC LIMIT ".$limit." OFFSET ".$offset;
        //     // query statement
        //     $stmt =  $conn->query($sql);
        //     // check if statement is successfil
        //     if($stmt){
        //         $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //     }
        //     //"st.id,st.author,st.issue_id,st.status,st.is_Escalated,st.is_Archived,st.assigned_agent,st.created_on,st.last_updated_on,st.assigned_on,st.has_AuthorTakenAction,hu.first_name,hu.last_name"

        //     // $sql = "SELECT ".$sqlType." FROM ".$this->table." st RIGHT JOIN ticket_assignment ta ON st.id = ta.support_ticket LEFT JOIN hh_user hu ON  ta.newly_assigned_agent = hu.user_id WHERE ta.previous_agent = :id GROUP BY ta.support_ticket ".($count == true?";":(" ORDER BY st.id DESC LIMIT ".$limit." OFFSET ".$offset.";"));
        //     // // Prepare statement
        //     // $stmt =  $conn->prepare($sql);
        //     // $result = "";

        //     // Only fetch if prepare succeeded
        //     // if ($stmt !== false) {
        //     //     $stmt->bindparam(':id', $id);
        //     //     $stmt->execute();
        //     //     $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //     // }
        // }

        // // Return all 
        // if($id == null && $category == null && $agent_name == null && $author_name == null){
        //     $sql = "SELECT * FROM ".$this->table." ORDER BY id DESC LIMIT ".$limit." OFFSET ".$offset;
        //     // query statement
        //     $stmt =  $conn->query($sql);
        //     // check if statement is successfil
        //     if($stmt){
        //         $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //     }
        // }
        
        $conn=null;
        $db=null;

        $ModelResponse =  array(
            "success"=>true,
            "data"=>$result
            // "data"=>"testSQL"
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
//  April 26 2022


// Get basic ticket info from db
// @desc    gets basic db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_ticket_base_info($id){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SELECT st.id, st.author, st.issue_id, st.status, st.is_Escalated, st.is_Archived, st.has_images, st.assigned_agent, st.created_on, st.last_updated_on, st.assigned_on, st.has_AuthorTakenAction,
        CONCAT(hh.last_name,', ',hh.first_name) as author_name,
        CONCAT(hh2.last_name,', ',hh2.first_name) as agent_name,
        stsc.subcategory as category_text,
        stst.status as status_text
        FROM ".$this->table." st 
        LEFT JOIN  hh_user hh ON st.author = hh.user_id
        LEFT JOIN  hh_user hh2 ON st.assigned_agent = hh2.user_id
        LEFT JOIN support_ticket_subcategory stsc ON st.issue_id = stsc.id
        LEFT JOIN support_ticket_status stst ON st.status = stst.id
        WHERE st.id = :id";
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


// Get ticket history info from db
// @desc    gets ticket history db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_ticket_history($id){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SELECT * FROM ticket_actions WHERE support_ticket = :id  ORDER BY id DESC;";
        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $stmt=null;
        $db=null;
        
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