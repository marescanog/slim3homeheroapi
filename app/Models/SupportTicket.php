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

        // Registration/Verification, Customer Support, Technical Support, supervisor, admin, superadmin, manager
        $roleSubTypes = ["1,2,3","4,5,6,7,8,9","10,11,12,13,14,15,16","1,2,3,4,5,6,7,8,9,10,11,12,13,14,15","12,14,15,19","12,14,15,16,19","17"];

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

        $sql = "SELECT st.id, st.author, st.issue_id, st.status, st.is_Escalated, st.is_Archived, st.has_images, st.assigned_agent, st.created_on, st.last_updated_on, st.assigned_on, st.has_AuthorTakenAction, st.author_Description,
        CONCAT(hh.last_name,', ',hh.first_name) as author_name,
        CONCAT(hh2.last_name,', ',hh2.first_name) as agent_name,
        sa.email as agent_email,
        stsc.subcategory as category_text,
        stst.status as status_text
        FROM ".$this->table." st 
        LEFT JOIN  hh_user hh ON st.author = hh.user_id
        LEFT JOIN  hh_user hh2 ON st.assigned_agent = hh2.user_id
        LEFT JOIN  support_agent sa ON hh2.user_id = sa.id
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


// Get ticket history info from db
// @desc    gets nbi info db info
// @params  support ticket id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_nbi_info($id){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SELECT ni.id, ni.clearance_no, ni.expiration_date, ni.is_deleted, ni.worker_id, ni.created_on, ni.is_verified, ni.support_ticket,
        CONCAT(hh.last_name, ', ', hh.first_name) AS worker_name,
        nif.file_id, f.file_name, f.file_path
        FROM `nbi_information` ni
        LEFT JOIN hh_user hh ON ni.worker_id = hh.user_id
        LEFT JOIN nbi_files nif ON ni.id = nif.NBI_id
        LEFT JOIN file f ON nif.file_id = f.id
        WHERE support_ticket = :id
        ORDER BY created_on DESC";

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



// @name    assigns a ticket with an agent ID, adds a ticket action and a new ticket assignment into table
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function assign_ticket($userID,$ticketID,$actionID=2,$description="",$stat=2,$newAgent=null,$prevAgent=null,$transferReason=null){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SET @@session.time_zone = '+08:00';
        BEGIN;
            UPDATE support_ticket s
            SET s.status = :stat, s.assigned_agent = :userID, s.last_updated_on = now(), s.assigned_on = now()
            WHERE s.id = :ticketID;

            INSERT INTO ticket_actions(action_taken, system_generated_description, support_ticket) VALUES (:actionID,:description,:sticketID);

            INSERT INTO ticket_assignment(support_ticket, date_assigned, newly_assigned_agent, previous_agent, transfer_reason) VALUES (:sticketID2, now(), :newAgent,:prevAgent,:reason);
        COMMIT;
        ";

        $new =  $newAgent==null?$userID:$newAgent;

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";
        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':userID', $userID);
            $stmt->bindparam(':stat', $stat);
            $stmt->bindparam(':ticketID', $ticketID);
            $stmt->bindparam(':actionID', $actionID);
            $stmt->bindparam(':description', $description);
            $stmt->bindparam(':sticketID', $ticketID);
            $stmt->bindparam(':sticketID2', $ticketID);
            $stmt->bindparam(':newAgent', $new);
            $stmt->bindparam(':prevAgent', $prevAgent);
            $stmt->bindparam(':reason', $transferReason);
            $result = $stmt->execute();
        } else {
            $result = "PDO Error";
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

// ===================================================================
// May 10 2022


// Additional Notes:
//      Affected Tables are:
//          nbi_information, hh_user, support_ticket, ticket_actions
// Get ticket history info from db
// @desc    gets ticket history db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function update_worker_registration($agentID, $ticketID, $workerID, $nbiID, $option, $comment = null){

    try{
        $db = new DB();
        $conn = $db->connect();

        $verifyNum = -1; // Default decline = -1, accept = 1, not graded = 0
        if($option == 1){
            $verifyNum = 1;
        } 

        $verifyUser = 3; // 1-Pending Verification, 2-Verified, 3-Declined
        if($option == 1){
            $verifyUser = 2;
        } 

        $sysDes = "AGENT #".$agentID." ".($verifyNum == -1 ? " DECLINED": " APPROVED")." WORKER #".$workerID." APPLICATION";

        // CREATE query
        $sql = "";

        // STEPS
        // 1. UPDATE NBI - > verified
        // 2. UPDATE WORKER ? USER -> verified
        // 3. ADD ACTION
        // 4. UPDATE TICKET CLOSED & Verified + TIME
        $sql = "SET @@session.time_zone = '+08:00';
        BEGIN;
            UPDATE nbi_information ni SET ni.is_verified = :verifyNum WHERE ni.id = :nbiID;

            UPDATE hh_user hh SET hh.user_status_id = :verifyUser WHERE hh.user_id = :workerID;

            UPDATE support_ticket st SET st.status = 4, st.last_updated_on = now(), st.has_AuthorTakenAction = 0 WHERE st.id = :ticketID;

            INSERT INTO ticket_actions(action_taken, system_generated_description,agent_notes, support_ticket) VALUES (7,:description,:notes,:sticketID);
        COMMIT;
        ";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':nbiID', $nbiID);
            $stmt->bindparam(':verifyNum', $verifyNum);
            $stmt->bindparam(':workerID', $workerID);
            $stmt->bindparam(':verifyUser', $verifyUser);
            $stmt->bindparam(':ticketID', $ticketID);
            $stmt->bindparam(':description', $sysDes );
            $stmt->bindparam(':notes', $comment);
            $stmt->bindparam(':sticketID', $ticketID);
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




// Get ticket history info from db
// @desc    gets ticket history db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function comment($ticketID, $workerID, $comment, $notify = null){

    try{
        $systemGenMessage = 'AGENT #'.$workerID.' ADDED NOTES TO TICKET #'.$ticketID;
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SET @@session.time_zone = '+08:00';
            BEGIN;
            UPDATE support_ticket st SET st.last_updated_on = now() WHERE st.id = :ticketID;
            INSERT INTO ticket_actions (action_taken, system_generated_description, agent_notes, support_ticket)
            VALUES (8, :sysMessage , :comment, :id);
            COMMIT;
        ";

        if($notify != null){
            $systemGenMessage = 'AGENT #'.$workerID.' REQUESTED CUSTOMER FOLLOW UP FOR TICKET #'.$ticketID;
            $sql = "SET @@session.time_zone = '+08:00';
                BEGIN;
                    UPDATE support_ticket st SET st.last_updated_on = now(), st.has_AuthorTakenAction = 2 WHERE st.id = :ticketID;
                    INSERT INTO ticket_actions (action_taken, system_generated_description, agent_notes, support_ticket)
                    VALUES (9, :sysMessage , :comment, :id);
                COMMIT;
            ";
        }

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':id', $ticketID);
            $stmt->bindparam(':comment', $comment);
            $stmt->bindparam(':sysMessage', $systemGenMessage);
            $stmt->bindparam(':ticketID', $ticketID);
            $result = $stmt->execute();
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




// ----- May 10 ------------------------------


// Get ticket comment history info from db
// @desc    gets ticket history db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_ticket_comment_history($id){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SELECT * FROM `ticket_actions` WHERE support_ticket = :id AND agent_notes IS NOT NULL ORDER BY id DESC;";
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


// ----- May 11 ------------------------------


// Get ticket comment history info from db
// @desc    gets ticket history db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_ticket_transfer_history($id){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SELECT ta.id, ta.support_ticket, ta.date_assigned, ta.newly_assigned_agent, ta.previous_agent, ta.transfer_reason,
        CONCAT(hh.last_name, ', ', hh.first_name) as new_agent_name,
        CONCAT(hh2.last_name, ', ', hh2.first_name) as prev_agent_name,
        tr.reason as reason_text
        FROM `ticket_assignment` ta 
        LEFT JOIN hh_user hh ON ta.newly_assigned_agent = hh.user_id
        LEFT JOIN hh_user hh2 ON ta.previous_agent = hh2.user_id
        LEFT JOIN ticket_transfer_reason tr ON ta.transfer_reason = tr.id
        WHERE ta.support_ticket = :id
        ORDER BY id DESC;";

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




// ----- May 14 ------------------------------


// Get bill info from db
// @desc    gets ticket history db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_bill_info($id){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SELECT bi.bill_id FROM `bill_issues` bi WHERE bi.support_ticket_id = :id;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";
        $bill_id = null;

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $bill_id = count($result) <= 0 ? null :$result[0]['bill_id'];
        }

        $d = [];

        if(count($result) <= 0 && $bill_id == null){
            $d['bill_details'] = null;
        } else {
            $sql2 = "SELECT b.id as bill_id, b.job_order_id, jo.job_post_id,
            b.worker_id, hhw.first_name as worker_fname, hhw.last_name as worker_lname, hhw.phone_no as worker_phone_no,
            b.homeowner_id, hhh.first_name as ho_fname, hhh.last_name as ho_lname, hhh.phone_no as ho_phone_no,
            b.payment_method_id, pm.payment_method,
            b.bill_status_id, bstat.status,
            b.remarks as bill_remarks, b.hours_readjustment, b.total_price_billed, b.is_received_by_worker, 
            b.created_on as bill_created_on, b.date_time_completion_paid,
            
            jo.job_order_status_id, jostat.status as job_order_status,
            jo.date_time_start as job_time_start, jo.date_time_closed as job_time_end, jo.isRated, jo.is_deleted, jo.created_on as job_created_on, jo.order_cancellation_reason, jo.cancelled_by,
            
            jp.job_post_name, jp.job_description as post_description, jp.job_post_status_id, jpstat.status as job_post_status,
            jp.rate_offer as post_offer, jp.rate_type_id, jprt.type as post_rate_type,
            jp.job_size_id, josize.job_order_size,
            jp.required_expertise_id, jpt.type as job_type, jpt.expertise as expertise_id, jpe.expertise as job_expertise,
            jp.home_id, h.street_no, h.street_name,ht.home_type_name, hb.barangay_name, hc.city_name,
            
            jp.is_exact_schedule as post_is_exact_schedule, jp.preferred_date_time as post_preferred_date_time, jp.date_time_closed as post_time_closed, jp.cancellation_reason as post_cancellation_reason, jp.created_on as post_created_on
            
            FROM `bill` b 
            LEFT JOIN job_order jo ON b.job_order_id = jo.id
            LEFT JOIN job_post jp ON jo.job_post_id = jp.id
            LEFT JOIN hh_user hhw ON b.worker_id = hhw.user_id
            LEFT JOIN hh_user hhh ON b.homeowner_id = hhh.user_id
            LEFT JOIN payment_method pm ON b.payment_method_id = pm.id
            LEFT JOIN bill_status bstat ON b.bill_status_id = bstat.id
            LEFT JOIN job_order_status jostat ON jo.job_order_status_id = jostat.id
            LEFT JOIN job_order_size josize ON jp.job_size_id = josize.id
            LEFT JOIN project_type jpt ON jp.required_expertise_id = jpt.id
            LEFT JOIN expertise jpe ON jpt.expertise = jpe.id
            LEFT JOIN job_post_status jpstat ON jp.job_post_status_id = jpstat.id
            LEFT JOIN rate_type jprt ON jp.rate_type_id = jprt.id
            LEFT JOIN home h ON jp.home_id = h.id
            LEFT JOIN home_type ht ON h.home_type = ht.id
            LEFT JOIN barangay hb ON h.barangay_id = hb.id
            LEFT JOIN city hc ON hb.city_id = hc.id
            WHERE b.id = :bill_ID;";
            // Prepare statement
            $stmt2 =  $conn->prepare($sql2);
            $result2 = "";
            // Only fetch if prepare succeeded
            if ($stmt2 !== false) {
                $stmt2->bindparam(':bill_ID', $bill_id);
                $stmt2->execute();
                $result2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
            }
            $d['bill_details'] = $result2;
        }

        $stmt=null;
        $db=null;
        $conn=null;

        $ModelResponse =  array(
            "success"=>true,
            "data"=>   $d
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


// Get bill info from db
// @desc    process_bill
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
// $agent_ID_currrent, $ticket_id, $payment_method, $inpt_bill_status, $fee_adjustment, 1, $comment
public function process_bill($agentID, $ticketID, $paymentID = null, $statusID = null, $feeAdjust = null, $comment = null){
    try{

        if($paymentID == null && $statusID == null && ($feeAdjust == null || $feeAdjust == "0" || $feeAdjust <= 0)){
            return  array(
                "success"=>false,
                "data"=>"Incomplete parameters provided for update: Please provide payment method, status or fee adjustment.",
                "err"=>"1"
            );
        }

        if($comment == null){
            return  array(
                "success"=>false,
                "data"=>"Incomplete parameters provided for update: Please provide a reason for editing the bill.",
                "err"=>"2"
            );
        }

        if($feeAdjust != null && ($feeAdjust == "0" || $feeAdjust <= 0)){
            return  array(
                "success"=>false,
                "data"=>"Incomplete parameters provided for update: fee adjustment cannot be zero or negative.",
                "err"=>"3"
            );
        }

        $db = new DB();
        $conn = $db->connect();

        // // STEPS
        // // 1. UPDATE BILL 
        // // 2. ADD ACTION
        // // 3. UPDATE TICKET CLOSED & Verified + TIME (In Another Function Since It wouldnt make sense)

        // CREATE query
        $sql_billID = "SELECT bi.bill_id FROM `bill_issues` bi WHERE bi.support_ticket_id = :id;";

        // Prepare statement
        $stmt_billID =  $conn->prepare($sql_billID);
        $result_billID = "";
        $bill_id = null;

        // Only fetch if prepare succeeded
        if ($stmt_billID !== false) {
            $stmt_billID->bindparam(':id', $ticketID);
            $stmt_billID->execute();
            $result_billID = $stmt_billID->fetchAll(\PDO::FETCH_ASSOC);
            $bill_id = count($result_billID) <= 0 ? null :$result_billID[0]['bill_id'];
        }
     
            // CREATE query to get bill details
            $sql_bill_info = "SELECT b.payment_method_id, b.bill_status_id, b.total_price_billed FROM `bill` b WHERE b.id = :id;";

            // Prepare statement
            $stmt_bill_info =  $conn->prepare($sql_bill_info);
            $result_bill_info = null;
    
            // Only fetch if prepare succeeded
            if ($stmt_bill_info !== false) {
                $stmt_bill_info->bindparam(':id', $bill_id);
                $stmt_bill_info->execute();
                $result_bill_info = $stmt_bill_info->fetchAll(\PDO::FETCH_ASSOC);
                $result_bill_info = count($result_bill_info) <= 0 ? null : $result_bill_info;
            }

            // If bill not found return error
            if($bill_id == null || $result_bill_info == null){
                return  array(
                    "success"=>false,
                    "data"=>"Bad Request: Bill Not Found.",
                    "err"=>"4"
                );
            }

            // Set To Null when values have not been changed
            if($paymentID == $result_bill_info[0]["payment_method_id"]){
                $paymentID = null;
            }

            if($statusID == $result_bill_info[0]["bill_status_id"]){
                $statusID = null;
            }

            if($feeAdjust == $result_bill_info[0]["total_price_billed"]){
                $feeAdjust = null;
            }
         
            if($paymentID == null && $statusID == null && $feeAdjust == null){
                return  array(
                    "success"=>false,
                    "data"=>"Values submited are the same with current record: Please provide a different value for payment method, status or fee adjustment.",
                    "err"=>"5"
                );
            }

        $d = [];

        // Only update bill if the bill is found
        if(count($result_billID) <= 0 && $bill_id == null){
            $d['update_details'] = null;
        } else {
            // After finding the bill ID, update the bill.
            // Construct a System Description and sql based on
                // $paymentID = null, $statudID = null, $feeAdjust = null
            
            $sysDes = "AGENT #".$agentID." MODIFIED BILL #".str_pad($bill_id, 5, "0", STR_PAD_LEFT).". (";
            $pay_mess = "PAYMENT METHOD CHANGED TO ";
            $pay_arr = array("","CASH","CREDIT","PAYPAL");
            $sta_mess = "STATUS UPDATED TO ";
            $sta_arr = array("","PENDING","PAID","CANCELLED");
            $fee_mess = "FEE CHANGED TO ";

            if($paymentID != null){
                $sysDes = $sysDes.$pay_mess.$pay_arr[$paymentID];
            }

            if($statusID != null){
                $sysDes = $sysDes.($paymentID != null ? ", " : "").$sta_mess.$sta_arr[$statusID];
            }

            if($feeAdjust != null){
                $sysDes = $sysDes.($statusID != null ? ", " : ($paymentID != null ? ", " : "")).$fee_mess.$feeAdjust;
            }

            $sysDes = $sysDes.")";
            $d['update_details'] = $bill_id;
            $d['system_description'] = $sysDes;

            // // CREATE query
            $sql = "";
            $sql = "SET @@session.time_zone = '+08:00'; BEGIN; UPDATE bill b SET";

            $pay_sql = " b.payment_method_id = :payID";
            $stat_sql = " b.bill_status_id = :statID";
            $fee_sql = " b.total_price_billed = :fee";

            if($paymentID != null){
                $sql = $sql.$pay_sql.($statusID != null ? ", " : ($feeAdjust != null ? ", " : ""));
            }

            if($statusID != null){
                $sql =  $sql.$stat_sql.($feeAdjust != null ? ", " : "");
            }

            if($feeAdjust != null){
                $sql = $sql.$fee_sql;
            }

            $sql=$sql."  WHERE b.id = :billID;
                UPDATE support_ticket st SET st.last_updated_on = now(), st.has_AuthorTakenAction = 1 WHERE st.id = :ticketID;
                INSERT INTO ticket_actions (action_taken, system_generated_description, agent_notes, support_ticket)
                VALUES (3, :sysMessage , :comment, :ticketID2);
                COMMIT;
            ";

            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";

            // Only bind if prepare succeeded
            if ($stmt !== false) {
                if($paymentID != null){
                    $stmt->bindparam(':payID', $paymentID);
                }
                if($statusID != null){
                    $stmt->bindparam(':statID', $statusID);
                }
                if($feeAdjust != null){
                    $stmt->bindparam(':fee', $feeAdjust);
                }
                $stmt->bindparam(':billID', $bill_id);
                $stmt->bindparam(':ticketID', $ticketID);
                $stmt->bindparam(':sysMessage', $sysDes);
                $stmt->bindparam(':comment', $comment);
                $stmt->bindparam(':ticketID2', $ticketID);
              
                $result = $stmt->execute();
            }

            // Add Action
            // $d['result'] = $result;
            // $d['sql'] = $sql;
            $params = [];
            $params["paymentID"] = $paymentID;
            $params["statusID"] = $statusID;
            $params["fee"] = $feeAdjust;
            $params["billID"] = $bill_id;
            $params["ticketID"] = $ticketID;
            $params["sysmessage"] = $sysDes;
            $params["comment"] = $comment;
            $params["ticketID2"] = $ticketID;
        }

        $stmt=null;
        $db=null;
        
        $conn=null;
        $db=null;

        $ModelResponse =  array(
            "success"=>true,
            "data"=>$result,
            // "params"=>$params,
            // "sql"=>$sql,
            // "other"=>    $result_bill_info
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


// ----- May 15 ------------------------------


// Update support ticket info in db with closed status
// @desc    updates support ticket info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function close_ticket($agentID, $ticketID, $status, $comment){
    try{

        $db = new DB();
        $conn = $db->connect();

        $stat = $status == 2 ? 4 : 3;

        $sysDes = "AGENT #".$agentID." MARKED TICKET AS ".($status == 2 ? "CLOSED": "RESOLVED");

        // CREATE query
        $sql = "SET @@session.time_zone = '+08:00'; BEGIN;
            UPDATE support_ticket st SET st.status = :status, st.last_updated_on = now(), st.has_AuthorTakenAction = 1 WHERE st.id = :ticketID; 
            INSERT INTO ticket_actions (action_taken, system_generated_description, agent_notes, support_ticket)
            VALUES (7, :sysMessage , :comment, :ticketID2);
            COMMIT;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':status', $stat);
            $stmt->bindparam(':ticketID', $ticketID);
            $stmt->bindparam(':sysMessage',  $sysDes);
            $stmt->bindparam(':comment', $comment);
            $stmt->bindparam(':ticketID2', $ticketID);
            $result = $stmt->execute();
        }

        $stmt=null;       
        $conn=null;
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



// Update  job order based on support ticket 
// @desc    gets job order including job post
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_joborder_from_support_ticket($ticketID){
    try{

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql_jo = "SELECT joi.job_order_id FROM `job_order_issues` joi WHERE joi.support_ticket_id = :id;";

        // Prepare statement
        $stmt_jo =  $conn->prepare($sql_jo);
        $result_jo = "";
        $bill_id = null;

        // Only fetch if prepare succeeded
        if ($stmt_jo !== false) {
            $stmt_jo->bindparam(':id', $ticketID);
            $stmt_jo->execute();
            $result_jo = $stmt_jo->fetchAll(\PDO::FETCH_ASSOC);
            $job_order_id = count($result_jo) <= 0 ? null : $result_jo[0]['job_order_id'];
        }

        $d = [];


        if($result_jo == null || $job_order_id == null){
            return  array(
                "success"=>false,
                "data"=>"Bad Request: Job Order Not Found.",
                "err"=>"1"
            );
        }

        // Prepare another statement when job order is found
        $sql = "SELECT jo.id as job_order_id, jo.order_cancellation_reason, jo.cancelled_by,
        jo.worker_id, hh.first_name as worker_fname, hh.last_name as worker_lname, hh.phone_no as worker_phone,
        jo.homeowner_id, hh2.first_name as ho_fname, hh2.last_name as ho_lname, hh2.phone_no as ho_phone,
        jo.job_order_status_id, jos.status as job_order_status_text,
        jo.date_time_start as job_start, jo.date_time_closed as job_end, jo.isRated, jo.created_on as job_order_created_on, jo.order_cancellation_reason, jo.cancelled_by,
        jo.job_post_id, jp.job_post_name, jp.job_description, jp.is_exact_schedule, jp.preferred_date_time as initial_schedule, jp.date_time_closed as job_post_closed_on, jp.cancellation_reason, jp.created_on as job_post_created_on,
        h.id as home_id, h.street_no, h.street_name, hb.barangay_name, hc.city_name, h.home_type as home_type_id, ht.home_type_name,
        jp.job_size_id, josize.job_order_size,
        pt.type as job_subcategory, e.expertise as job_category,
        jp.job_post_status_id, jpstat.status as job_post_stat,
        jp.rate_offer, jp.rate_type_id, rt.type as rate_type_name
        FROM job_order jo
        LEFT JOIN hh_user hh ON jo.worker_id = hh.user_id
        LEFT JOIN hh_user hh2 ON jo.homeowner_id = hh2.user_id
        LEFT JOIN job_order_status jos ON jo.job_order_status_id = jos.id
        LEFT JOIN job_post jp ON jo.job_post_id = jp.id
        LEFT JOIN home h ON jp.home_id = h.id
        LEFT JOIN barangay hb ON h.barangay_id = hb.id
        LEFT JOIN city hc ON hb.city_id = hc.id
        LEFT JOIN home_type ht ON h.home_type = ht.id
        LEFT JOIN job_order_size josize ON jp.job_size_id = josize.id
        LEFT JOIN project_type pt ON jp.required_expertise_id = pt.id
        LEFT JOIN expertise e ON pt.expertise = e.id
        LEFT JOIN job_post_status jpstat ON jp.job_post_status_id = jpstat.id
        LEFT JOIN rate_type rt ON jp.rate_type_id = rt.id
        WHERE jo.id = :joid;";
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':joid', $job_order_id);
            $result = $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $stmt=null;       
        $conn=null;
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


// May 17
// GET  job order based on support ticket, only relevant information for edit validation
// @desc    gets job order including job post
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_joborder_from_support_ticket_LIGHT($ticketID){
    try{

        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql_jo = "SELECT joi.job_order_id FROM `job_order_issues` joi WHERE joi.support_ticket_id = :id;";

        // Prepare statement
        $stmt_jo =  $conn->prepare($sql_jo);
        $result_jo = "";
        $bill_id = null;

        // Only fetch if prepare succeeded
        if ($stmt_jo !== false) {
            $stmt_jo->bindparam(':id', $ticketID);
            $stmt_jo->execute();
            $result_jo = $stmt_jo->fetchAll(\PDO::FETCH_ASSOC);
            $job_order_id = count($result_jo) <= 0 ? null : $result_jo[0]['job_order_id'];
        }

        $d = [];


        if($result_jo == null || $job_order_id == null){
            return  array(
                "success"=>false,
                "data"=>"Bad Request: Job Order Not Found.",
                "err"=>"1"
            );
        }

        // Prepare another statement when job order is found
        $sql = "SELECT jo.id as job_order_id, jo.worker_id, jo.homeowner_id, jo.job_order_status_id, 
        jo.job_order_status_id, jo.date_time_start as job_start, jo.date_time_closed as job_end, jo.isRated, jo.created_on as job_order_created_on, jo.order_cancellation_reason, jo.cancelled_by,
        jo.job_post_id, h.id as home_id
        FROM job_order jo
        LEFT JOIN job_post jp ON jo.job_post_id = jp.id
        LEFT JOIN home h ON jp.home_id = h.id
        WHERE jo.id = :joid;";
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':joid', $job_order_id);
            $result = $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $stmt=null;       
        $conn=null;
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



// May 17
// UPDATE  job order based on support ticket, 
// @desc    gets job order including job post
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function edit_job_order_issue($agentID, $ticketID, $job_order_ID = null, $job_post_ID = null, $job_order_status = null, $jo_start_date_time  = null, $jo_end_date_time = null, $jo_address_submit = null, $comment = null, $previouslyCancelled = false, $type = 1){
    try{

        $db = new DB();
        $conn = $db->connect();

        $hasjob_update = $job_order_status != null || $jo_start_date_time  != null || $jo_end_date_time != null;

        // Validation - check comment
        if($comment == null){
            return array(
                "success"=>false,
                "data"=>"Incomplete Details: No reason Provided for update Query",
                "err"=>1
            );
        }

        // Validation - check job order ID
        if($job_order_ID == null){
            return array(
                "success"=>false,
                "data"=>"Incomplete Details: No Job Order ID Provided for Query",
                "err"=>1
            );
        }

        if($jo_address_submit != null && $job_post_ID == null){
            return array(
                "success"=>false,
                "data"=>"Update Error: Unable to find Job Post ID for Query",
                "err"=>1
            );
        }

        // Validation - check if all parameters submitted are null
        if($type == 1 && $job_order_status == null && $jo_start_date_time  == null && $jo_end_date_time == null && $jo_address_submit == null){
            return array(
                "success"=>false,
                "data"=>"Incomplete Details: No Parameters Provided for Query",
                "err"=>2
            );
        }

        // Validation - check if already cancelled before on a cancellation request
        if($type == 2 && $previouslyCancelled == true){
            return array(
                "success"=>false,
                "data"=>"This order has already been cancelled",
                "err"=>2
            );
        }

        // Validation - check job order status ID range
        if($job_order_status != null){
            if($job_order_status > 3 || $job_order_status < 1){
                return array(
                    "success"=>false,
                    "data"=>"Incomplete Details: Job Order Status ID out of range. ".var_dump($job_order_status),
                    "err"=>1,
                );
            }
        }
        // $result = [];
        $result = "";
        // ------------------------------------------------
        // // STEPS
        // // 1. UPDATE JOB ORDER OR POST IF APPLICABLE
        // // 2. If job order status = 3 include update for job order table order cancellation reason (comment) & cancelled by (agentID)  
        // // 3. ADD ACTION

        $sysDes = "AGENT #".$agentID." ".($job_order_status!=3?"MODIFIED":"CANCELLED")." JOB ORDER #".str_pad($job_order_ID, 5, "0", STR_PAD_LEFT).". (";
        $sql = "SET @@session.time_zone = '+08:00'; BEGIN; ";

        // $update_jo_sql = "UPDATE job_order jo SET jo.job_order_status_id = 1 WHERE jo.id = 15;";
        $hasPrevParamUpdate = false;
        if($hasjob_update == true){
            $sql = $sql."UPDATE job_order jo SET";

            // Set Job Order Status
            if($job_order_status != null){
                $jobStatArr = array("","CONFIRMED","COMPLETED","CANCELLED");
                if($previouslyCancelled == true){
                    // ADD TO SQL
                    $sql = $sql." jo.job_order_status_id =:jo_stat";
                    if($job_order_status != 3){
                        $sql = $sql.", jo.order_cancellation_reason = null, jo.cancelled_by = null";
                    } else {
                        $sql = $sql.", jo.order_cancellation_reason = :cancelReason, jo.cancelled_by = :cancelAgentID";
                    }
                    // Add TO system description
                    $sysDes = $sysDes."STATUS RESTORED TO ".$jobStatArr[$job_order_status];
                }else{
                    // ADD TO SQL
                    $sql = $sql." jo.job_order_status_id = :jo_stat";
                    if($job_order_status == 3){
                        $sql = $sql.", jo.order_cancellation_reason = :cancelReason, jo.cancelled_by = :cancelAgentID";
                    }
                    // Add TO system description
                    if($job_order_status != 3){
                        $sysDes = $sysDes."STATUS UPDATED TO ".$jobStatArr[$job_order_status];
                    } else {
                        $sysDes = $sysDes."STATUS UPDATE";
                    }
                }
                $hasPrevParamUpdate = true;
            }    

            // Set Job Order Start Date
            if($jo_start_date_time != null){
                // ADD TO SQL
                $sql = $sql.($hasPrevParamUpdate==true?",":"")." jo.date_time_start = :jo_time_start";
                // Add TO system description
                $sysDes = $sysDes.($hasPrevParamUpdate==true?", ":"")."START TIME MODIFIED TO ".$jo_start_date_time;
                $hasPrevParamUpdate = true;
            }

            // Set Job Order End Date
           if($jo_end_date_time != null){
                // ADD TO SQL
                $sql = $sql.($hasPrevParamUpdate==true?", ":"")." jo.date_time_closed = :jo_time_end";
                // Add TO system description
                $sysDes = $sysDes.($hasPrevParamUpdate==true?", ":"")."END TIME MODIFIED TO ".$jo_end_date_time;
                $hasPrevParamUpdate = true;
            }

            $sql = $sql." WHERE jo.id = :joID;";
        }

        // Update address in post
        if($jo_address_submit != null && $job_post_ID != null){
            $sql = $sql." UPDATE job_post jp SET jp.home_id = :homeID WHERE jp.id = :postID;";
            $sysDes = $sysDes.($hasPrevParamUpdate==true?", ":"")."ADDRESS CHANGED";
        }
        $sysDes = $sysDes.")";

        // UPDATE SUPPORT TICKET TABLE
        $sql = $sql." UPDATE support_ticket st SET st.last_updated_on = now() WHERE st.id = :t1;";

        // ADD ACTION
        $sql = $sql." INSERT INTO ticket_actions(action_taken, system_generated_description, agent_notes, support_ticket) VALUES (3,:description,:agentNotes,:t2);";
        
        // CLOSE QUERY
        $sql = $sql." COMMIT;";

        // $result["sql"]= $sql;
        // $result["sysDes"]= $sysDes;
        // $result["hasjob_update"]= $hasjob_update;

        // // Prepare statement
        $stmt =  $conn->prepare($sql);
        // $result = "";

        // if(false){
            // Only fetch if prepare succeeded
            if ($stmt !== null) {
                if($job_order_status != null){
                    $stmt->bindparam(':jo_stat', $job_order_status);
                    if($job_order_status==3 && $previouslyCancelled == false){
                        $stmt->bindparam(':cancelReason', $comment);
                        $stmt->bindparam(':cancelAgentID', $agentID);
                    } 
                }

                if($jo_start_date_time != null){
                    $stmt->bindparam(':jo_time_start', $jo_start_date_time);
                }

                if($jo_end_date_time != null){
                    $stmt->bindparam(':jo_time_end', $jo_end_date_time);
                }

                if($hasjob_update == true){
                    $stmt->bindparam(':joID', $job_order_ID);
                }

                // Update address in post
                if($jo_address_submit != null && $job_post_ID != null){
                    $sql = $sql." UPDATE job_post jp SET jp.home_id = :homeID WHERE jp.id = :postID;";
                    $stmt->bindparam(':homeID', $jo_address_submit);
                    $stmt->bindparam(':postID', $job_post_ID);
                }

                $stmt->bindparam(':t1', $ticketID);
                $stmt->bindparam(':description', $sysDes);
                $stmt->bindparam(':t2', $ticketID);
                $stmt->bindparam(':agentNotes', $comment);
                
                // $result["result"] = $stmt->execute();
                $result = $stmt->execute();
            }
        // }

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


// @desc    gets the hashed trans code value
    // @params  sup id and permissions id
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is the support account
    public function get_transCode($sup_id, $permissions_id)
    {
        try {
            $db = new DB();
            $conn = $db->connect();

            $result = "";

            // Prepare another statement when job order is found
            $sql = "SELECT * FROM override_codes oc WHERE oc.permissions_id = :perm_id AND oc.permissions_owner_id = :sup_id;";

            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':sup_id', $sup_id);
                $stmt->bindparam(':perm_id', $permissions_id);
                $result = $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
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

    // @desc    gets the hashed trans code value
    // @params  sup id and permissions id
    // @returns a Model Response object with the attributes "success" and "data"
    //          sucess value is true when PDO is successful and false on failure
    //          data value is the support account
public function sendNotif($supID, $ticketID, $notifType, $transferReason = null, $senderID, $permissionID, $comment)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $result = "";

            $transReason = $transferReason == null ? 5 : $transferReason;

            $notifTypesArr = array("FOLLOW-UP REQUEST","TRANSFER REQUEST","ESCALATION REQUEST","TICKET ACCESS REQUEST","OVERRIDE PERMISSIONS REQUEST","OVERRIDE PERMISSIONS NOTICE");
            $transReasArr = array("WRONG DEPARTMENT", "SUPERVISOR ESCALATION", "LEAVE OF ABSENCE", "ADMIN ESCALATION", "OTHER");
            $ticketActionArr = array( "14", // Follow Up
                                      "10", // Transfer
                                      "11", // Escalation
                                      "16", // Request Ticket Access
                                      "16", // Request Override Permissions
                                      "16" // Notice on Override Permissions - Refine Later
                                );

            $ticketActionID = $ticketActionArr[$notifType-1];

            $sysGen = "AGENT #163 ". $notifTypesArr[$notifType-1]." REASON-R". $transReason." ". $transReasArr[$transReason-1]." ON TICKET #".$ticketID;

            $sysGenNotif = "AGENT #163 ". $notifTypesArr[$notifType-1]." REASON-R". $transReason." ". $transReasArr[$transReason-1]." ON TICKET #".$ticketID.". (NOTES: ".$comment.")";
            
            // Prepare another statement when job order is found
            $sql = "SET @@session.time_zone = '+08:00'; 
            BEGIN;

            INSERT INTO ticket_actions 
            (action_taken, system_generated_description, agent_notes, support_ticket) 
            VALUES (:tktAction, :sysMessage , :comment, :ticketID2);

            INSERT INTO `support_notifications` 
            (`id`, `ticket_actions_id`, `recipient_id`, `support_ticket_id`, `notification_type_id`, 
            `generated_by`, `permissions_id`, `permissions_owner`, `system_generated_description`, 
            `has_taken_action`, `is_deleted`, `is_read`, `created_on`) 
            VALUES 
            (NULL, LAST_INSERT_ID(),:supID, :supTicketID, :notifType, 
            :userID, :permissionsID, :permissionsOwner, :sysGen, 
            '0', '0', '0', now());


            
            COMMIT;";

            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':supID', $supID);
                $stmt->bindparam(':supTicketID', $ticketID);
                $stmt->bindparam(':notifType', $notifType);
                $stmt->bindparam(':userID', $senderID);
                $stmt->bindparam(':permissionsID', $permissionID);
                $stmt->bindparam(':permissionsOwner', $supID);
                $stmt->bindparam(':sysGen', $sysGenNotif);
                $stmt->bindparam(':tktAction', $ticketActionID);
                $stmt->bindparam(':sysMessage', $sysGen);
                $stmt->bindparam(':comment', $comment);
                $stmt->bindparam(':ticketID2', $ticketID);
                $result = $stmt->execute();
            }

            $stmt = null;
            $db = null;

            // $params["supID"] = $supID;
            // $params["supTicketID"] = $ticketID;
            // $params["notifType"] = $notifType;
            // $params["userID"] = $senderID;
            // $params["permissionsID"] = $permissionID;
            // $params["permissionsOwner"] = $supID;
            // $params["sysGen"] = $sysGen;
            // $params["tktAction"] = $ticketActionID;
            // $params["sysMessage"] = $sysGen;
            // $params["comment"] = $comment;
            // $params["ticketID2"] = $ticketID;


            $ModelResponse =  array(
                "success" => true,
                "data" => $result,
                // "params" => $params
            );

            return $ModelResponse;
        } catch (\PDOException $e) {

            $ModelResponse =  array(
                "success" => false,
                "data" => $e->getMessage()
            );
    }}

// @desc    gets notifications for sup
// @params  sup id and permissions id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is the support account                                               $getRead = true, $getAll = false, $getDone = false,
  public function get_notifications($supID, $permissionsID = null, $supportTicketID = null, $retreivalType = 'all', $limit=10, $offset=0)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $stmt = "";
            $result = [];

            if($permissionsID != null && $supportTicketID != null){
                // For agent get specific using permisisons and supportID

                $sql="SELECT * FROM support_notifications sn 
                WHERE sn.recipient_id = :supID
                AND sn.support_ticket_id = :ticketID 
                AND sn.permissions_id = :permissionsID 
                AND sn.is_deleted = 0
                AND sn.has_taken_action = 0;";
                
                $stmt =  $conn->prepare($sql);

                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':supID', $supID);
                    $stmt->bindparam(':ticketID', $supportTicketID);
                    $stmt->bindparam(':permissionsID', $permissionsID);
                    $result = $stmt->execute();
                    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                }

            } else {
                // For Sup get multiple for dash list show
                //" ORDER BY st.id DESC LIMIT ".$limit." OFFSET ".$offset." $limit=1000,$offset=0

                // Default all
                switch($retreivalType){
                    case "new";
                        $sql=(($limit==null && $offset==null)?"SELECT COUNT(*) as total "
                        :"SELECT sn.id, sn.support_ticket_id ,sn.ticket_actions_id, sn.recipient_id, sn.notification_type_id, sn.generated_by, sn.permissions_id, sn.permissions_owner, sn.system_generated_description, sn.has_taken_action, sn.is_deleted, sn.is_read, sn.created_on, 
                        CONCAT(hh.first_name,' ',SUBSTRING(hh.last_name, 1, 1),'.') as sender ")
                        ."FROM support_notifications sn 
                        LEFT JOIN hh_user hh ON sn.generated_by = hh.user_id
                        WHERE sn.recipient_id = :supID 
                        AND sn.is_read = 0
                        AND sn.is_deleted = 0
                        AND sn.has_taken_action = 0 "
                        .(($limit==null && $offset==null)?";":"ORDER BY sn.id DESC LIMIT ".$limit." OFFSET ".$offset.";");
        
                        $stmt =  $conn->prepare($sql);
        
                        // Only fetch if prepare succeeded
                        if ($stmt !== false) {
                            $stmt->bindparam(':supID', $supID);
                            $result = $stmt->execute();
                            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                        }
                    break;
                    case "read";
                        //SELECT COUNT(*) FROM `support_ticket`
                        $sql=(($limit==null && $offset==null)?"SELECT COUNT(*) as total "
                        :"SELECT sn.id, sn.support_ticket_id ,sn.ticket_actions_id, sn.recipient_id, sn.notification_type_id, sn.generated_by, sn.permissions_id, sn.permissions_owner, sn.system_generated_description, sn.has_taken_action, sn.is_deleted, sn.is_read, sn.created_on, 
                        CONCAT(hh.first_name,' ',SUBSTRING(hh.last_name, 1, 1),'.') as sender ")
                        ."FROM support_notifications sn 
                        LEFT JOIN hh_user hh ON sn.generated_by = hh.user_id
                        WHERE sn.recipient_id = :supID 
                        AND sn.is_read = 1
                        AND sn.is_deleted = 0
                        AND sn.has_taken_action = 0 "
                        .(($limit==null && $offset==null)?";":"ORDER BY sn.id DESC LIMIT ".$limit." OFFSET ".$offset.";");
        
                        $stmt =  $conn->prepare($sql);
        
                        // Only fetch if prepare succeeded
                        if ($stmt !== false) {
                            $stmt->bindparam(':supID', $supID);
                            $result = $stmt->execute();
                            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                        }
                    break;
                    case "done";
                        $sql=(($limit==null && $offset==null)?"SELECT COUNT(*) as total "
                        :"SELECT sn.id, sn.support_ticket_id ,sn.ticket_actions_id, sn.recipient_id, sn.notification_type_id, sn.generated_by, sn.permissions_id, sn.permissions_owner, sn.system_generated_description, sn.has_taken_action, sn.is_deleted, sn.is_read, sn.created_on, 
                        CONCAT(hh.first_name,' ',SUBSTRING(hh.last_name, 1, 1),'.') as sender ")
                        ."FROM support_notifications sn 
                        LEFT JOIN hh_user hh ON sn.generated_by = hh.user_id
                        WHERE sn.recipient_id = :supID 
                        AND sn.is_deleted = 0
                        AND sn.has_taken_action = 1 "
                        .(($limit==null && $offset==null)?";":"ORDER BY sn.id DESC LIMIT ".$limit." OFFSET ".$offset.";");
        
                        $stmt =  $conn->prepare($sql);
        
                        // Only fetch if prepare succeeded
                        if ($stmt !== false) {
                            $stmt->bindparam(':supID', $supID);
                            $result = $stmt->execute();
                            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                        }
                    break;
                    default:
                        $sql=(($limit==null && $offset==null)?"SELECT COUNT(*) as total "
                        :"SELECT sn.id, sn.support_ticket_id ,sn.ticket_actions_id, sn.recipient_id, sn.notification_type_id, sn.generated_by, sn.permissions_id, sn.permissions_owner, sn.system_generated_description, sn.has_taken_action, sn.is_deleted, sn.is_read, sn.created_on, 
                        CONCAT(hh.first_name,' ',SUBSTRING(hh.last_name, 1, 1),'.') as sender ")
                        ."FROM support_notifications sn 
                        LEFT JOIN hh_user hh ON sn.generated_by = hh.user_id
                        WHERE sn.recipient_id = :supID 
                        AND sn.is_deleted = 0
                        AND sn.has_taken_action = 0 "
                        .(($limit==null && $offset==null)?";":"ORDER BY sn.id DESC LIMIT ".$limit." OFFSET ".$offset.";");
        
                        $stmt =  $conn->prepare($sql);
        
                        // Only fetch if prepare succeeded
                        if ($stmt !== false) {
                            $stmt->bindparam(':supID', $supID);
                            $result = $stmt->execute();
                            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                        }
                    break;
                }
                // if($getAll == true){
                //     $sql=(($limit==null && $offset==null)?"SELECT COUNT(*) as total "
                //     :"SELECT sn.id, sn.support_ticket_id ,sn.ticket_actions_id, sn.recipient_id, sn.notification_type_id, sn.generated_by, sn.permissions_id, sn.permissions_owner, sn.system_generated_description, sn.has_taken_action, sn.is_deleted, sn.is_read, sn.created_on, 
                //     CONCAT(hh.first_name,' ',SUBSTRING(hh.last_name, 1, 1),'.') as sender ")
                //     ."FROM support_notifications sn 
                //     LEFT JOIN hh_user hh ON sn.generated_by = hh.user_id
                //     WHERE sn.recipient_id = :supID 
                //     AND sn.is_deleted = 0
                //     AND sn.has_taken_action = 0 "
                //     .(($limit==null && $offset==null)?";":"ORDER BY sn.id DESC LIMIT ".$limit." OFFSET ".$offset.";");
    
                //     $stmt =  $conn->prepare($sql);
    
                //     // Only fetch if prepare succeeded
                //     if ($stmt !== false) {
                //         $stmt->bindparam(':supID', $supID);
                //         $result = $stmt->execute();
                //         $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                //     }
                // } else if($getDone == true){
                //     $sql=(($limit==null && $offset==null)?"SELECT COUNT(*) as total "
                //     :"SELECT sn.id, sn.support_ticket_id ,sn.ticket_actions_id, sn.recipient_id, sn.notification_type_id, sn.generated_by, sn.permissions_id, sn.permissions_owner, sn.system_generated_description, sn.has_taken_action, sn.is_deleted, sn.is_read, sn.created_on, 
                //     CONCAT(hh.first_name,' ',SUBSTRING(hh.last_name, 1, 1),'.') as sender ")
                //     ."FROM support_notifications sn 
                //     LEFT JOIN hh_user hh ON sn.generated_by = hh.user_id
                //     WHERE sn.recipient_id = :supID 
                //     AND sn.is_deleted = 0
                //     AND sn.has_taken_action = 1 "
                //     .(($limit==null && $offset==null)?";":"ORDER BY sn.id DESC LIMIT ".$limit." OFFSET ".$offset.";");
    
                //     $stmt =  $conn->prepare($sql);
    
                //     // Only fetch if prepare succeeded
                //     if ($stmt !== false) {
                //         $stmt->bindparam(':supID', $supID);
                //         $result = $stmt->execute();
                //         $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                //     }
                // }else{

                //     $getRead = $getRead == true ? 1 : 0;
                //     //SELECT COUNT(*) FROM `support_ticket`
                //     $sql=(($limit==null && $offset==null)?"SELECT COUNT(*) as total "
                //     :"SELECT sn.id, sn.support_ticket_id ,sn.ticket_actions_id, sn.recipient_id, sn.notification_type_id, sn.generated_by, sn.permissions_id, sn.permissions_owner, sn.system_generated_description, sn.has_taken_action, sn.is_deleted, sn.is_read, sn.created_on, 
                //     CONCAT(hh.first_name,' ',SUBSTRING(hh.last_name, 1, 1),'.') as sender ")
                //     ."FROM support_notifications sn 
                //     LEFT JOIN hh_user hh ON sn.generated_by = hh.user_id
                //     WHERE sn.recipient_id = :supID 
                //     AND sn.is_read = :isRead
                //     AND sn.is_deleted = 0
                //     AND sn.has_taken_action = 0 "
                //     .(($limit==null && $offset==null)?";":"ORDER BY sn.id DESC LIMIT ".$limit." OFFSET ".$offset.";");
    
                //     $stmt =  $conn->prepare($sql);
    
                //     // Only fetch if prepare succeeded
                //     if ($stmt !== false) {
                //         $stmt->bindparam(':supID', $supID);
                //         $stmt->bindparam(':isRead', $getRead );
                //         $result = $stmt->execute();
                //         $result = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
                //     }
                // }
                
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

// @desc    gets notifications by iD
// @params  notification ID
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is the support account   
  public function getNotificationUsingNotificationID($notifID){
        try {

            $db = new DB();
            $conn = $db->connect();

            $sql="SELECT * FROM support_notifications sn WHERE sn.id = :notifID;";
            
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':notifID', $notifID);
                $result = $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC); 
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

// @desc    gets the supervisor of the agent using ID
// @params  agent ID
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is the support account  
public function getTheSupervisorOfAgentUsingAgentID($agentID){
    try {

        $db = new DB();
        $conn = $db->connect();
        $result = "";

        // $sql="SELECT sa.supervisor_id, sa.role_type 
        // FROM support_agent sa 
        // WHERE sa.id = :agentID;";

        $sql="SELECT sa.id, sa.supervisor_id, sa.role_type, sa.is_deleted,
        hh.user_status_id as active_status
        FROM support_agent sa 
        LEFT JOIN hh_user hh ON sa.id = hh.user_id
        WHERE sa.id = :agentID;";
            
        $stmt =  $conn->prepare($sql);

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':agentID', $agentID);
            $result = $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC); 
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

// @desc    gets all the agents of a supervisor using sup ID and other parameters
// @params  supervisor ID, optional role, status, email, number
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is the list of all agents with agent ID first name, last name & role
  public function getAgentsOfSupervisor($supID, $role = null, $status = 2, $hasEmail = true, $hasNumber = true)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $sql = "SELECT sn.id, sn.role_type, ".($hasEmail == true ? "sn.email, ":"").
            "hh.first_name, hh.last_name, CONCAT(hh.last_name, ', ', hh.first_name) as full_name ".
            ($hasNumber == true ?", hh.phone_no ":"")
            ."FROM support_agent sn 
            LEFT JOIN hh_user hh ON sn.id = hh.user_id
            WHERE 
            hh.user_status_id = 2 AND ".
            ($role == null ? "":"sn.role_type = :roleType AND ")
            ."sn.is_deleted = 0 AND sn.supervisor_id =  :supID;";

            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':supID', $supID);
                if($role != null){
                    $stmt->bindparam(':roleType', $role);
                }
                $result = $stmt->execute();
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


// Get basic ticket info from db
//  No addtional user details just base ticket info
// @desc    gets basic db info
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function get_ticket_base_info_LIGHT($id){

    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SELECT * FROM ".$this->table." st WHERE st.id = :id;";

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



public function getAllAgentsUnderARole($role, $status = 2, $hasEmail = true, $hasNumber = true)
{
    try {

        $db = new DB();
        $conn = $db->connect();

        // $sql = "SELECT sa.id, sa.role_type, 
        // hh.first_name, hh.last_name, CONCAT(hh.last_name, ', ', hh.first_name) as full_name
        // FROM support_agent sa 
        // LEFT JOIN hh_user hh ON sa.id = hh.user_id
        // WHERE sa.role_type = 2;";

        $sql = "SELECT sn.id, sn.role_type, ".($hasEmail == true ? "sn.email, ":"").
        "hh.first_name, hh.last_name, CONCAT(hh.last_name, ', ', hh.first_name) as full_name ".
        ($hasNumber == true ?", hh.phone_no ":"")
        ."FROM support_agent sn 
        LEFT JOIN hh_user hh ON sn.id = hh.user_id
        WHERE 
        hh.user_status_id = :status AND sn.role_type = :roleType AND sn.is_deleted = 0;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':roleType', $role);
            $stmt->bindparam(':status', $status);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        $stmt=null;
        $conn=null;
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
    }
}

// ---------------------------------------------
//          MAY 22 , 2022
// ---------------------------------------------
  public function processTransferRequest($notification_ID, $support_ticket_ID, $chosen_agent,
        $from_agent, $transfer_reason_id = 5, $processorID, $transferToSup = 1, //1 - no, 2 - yes
        $externalTransfer = false, $processor_supID = null, $from_agent_sup = null, $chosen_agent_sup = null 
  ){
    try {       

        // NOTES AS GUIDANCE
        // Done getting tranfer reason, just set default to 5
        // Transfer reason is either 1  3 or 5, REASON-R is the separator and it is found in SYSGEN message of support notification
        // Transfer type - isInternal ? default is external
        // DONE - UPDATE NOTIFICATION ->  need notif ID (has_taken_action = 1, is_read = 1)
        // DONE - UPDATE SUPPORT TICKET -> need support id, assigned agent ( assigned agent, last updated on) 
        // INSERT INTO TICKET ASSIGNMENT -> support ticket, newly assigned agent, prevuos agent, transfer reason  (support ticket, newly assigned agent, prevuos agent, transfer reason)
        // DONE - INSERT INTO ACTION -> action_taken, system generated description, agent notes, support ticket ( action_taken, system generated description, agent notes, support ticket)
        // INSERT NOTIFICATION -> 123 LAST_INSERT_ID() -> only if external agent transfer
                // TRANSFER INTERNAL
                // TRANSFER EXTERNAL
        // actions is last before notification because you need the action id of the action if inserting into a new
        //      notification to notify the sup or manager of internal agent that you transferred their agent


        $db = new DB();
        $conn = $db->connect();

        $sql = "SET @@session.time_zone = '+08:00'; BEGIN;

        UPDATE support_notifications sn SET sn.is_read = 1, sn.has_taken_action = 1 WHERE sn.id = :notifID;

        UPDATE support_ticket st SET st.last_updated_on = now(), st.assigned_agent = :newAgent2 WHERE st.id = :supportTicketID;

        INSERT INTO `ticket_assignment` (`id`, `support_ticket`, `date_assigned`, `newly_assigned_agent`, 
        `previous_agent`, `transfer_reason`) VALUES (NULL, :supportTicketID2, now(), :newAgent, :pastAgent, :transferReasonID);

        INSERT INTO ticket_actions 
        (action_taken, system_generated_description, agent_notes, support_ticket) 
        VALUES (4, :sysgen , NULL, :supportTicketID3);

        SET @ticketActionID:=LAST_INSERT_ID();
        
        "
        // Send notification if: 
        //      - External transfer -> send to manager
        //      - Chosen agent is not my agent
        //      - From agent is not my agent


        // Send Notification to Manager on an External Transfer
        .($externalTransfer == false && $processor_supID == null ? "" :
        "INSERT INTO `support_notifications` 
        (`id`, `ticket_actions_id`, `recipient_id`, `support_ticket_id`, `notification_type_id`, 
        `generated_by`, `permissions_id`, `permissions_owner`, `system_generated_description`, 
        `has_taken_action`, `is_deleted`, `is_read`, `created_on`) 
        VALUES 
        (NULL, @ticketActionID, :notifRecipient, :supportTicketForNotif, 6, 
        :notifCreator, 1, :permissionsOwner, :sysGenEXTERNAL, 
        '0', '0', '0', now());"
        )

        // Send Notification if From agent is not my agent
        .($from_agent_sup == null || $processorID == $from_agent_sup ? "" : 
                "INSERT INTO `support_notifications` 
                (`id`, `ticket_actions_id`, `recipient_id`, `support_ticket_id`, `notification_type_id`, 
                `generated_by`, `permissions_id`, `permissions_owner`, `system_generated_description`, 
                `has_taken_action`, `is_deleted`, `is_read`, `created_on`) 
                VALUES 
                (NULL, @ticketActionID, :notifRecipientFrom, :supportTicketForNotifFrom, 6, 
                :notifCreatorFrom, :permissionIDFrom, :permissionsOwnerFrom, :sysGenEXTERNALFrom, 
                '0', '0', '0', now());"
         )

         // Send Notification if Chosen agent is not my agent
         // No need to send another notification if From agent Sup is the same as Chosen agent Sup
         // No need to send a notification to my sup (manager) if I am transfering the ticket to myself since external will always be flagged
         .(($chosen_agent_sup == null || $processorID == $chosen_agent_sup ) || ($from_agent_sup == $chosen_agent_sup) || ($chosen_agent == $processor_supID) ?  "" : 
         "INSERT INTO `support_notifications` 
         (`id`, `ticket_actions_id`, `recipient_id`, `support_ticket_id`, `notification_type_id`, 
         `generated_by`, `permissions_id`, `permissions_owner`, `system_generated_description`, 
         `has_taken_action`, `is_deleted`, `is_read`, `created_on`) 
         VALUES 
         (NULL, @ticketActionID, :notifRecipientChosen, :supportTicketForNotifChosen, 6, 
         :notifCreatorChosen, :permissionIDChosen, :permissionsOwnerChosen, :sysGenEXTERNALChosen, 
         '0', '0', '0', now());"
        )

        ."COMMIT;";

        $sysGen = "SUP #".$processorID." ACCEPTED TRANSFER REQUEST OF AGENT #".$from_agent." ON TICKET #".$support_ticket_ID.". TRANSFERRED TO AGENT #".$chosen_agent;

        if($transferToSup == 2){
            if($processorID == $chosen_agent){
                $sysGen = "SUP #".$processorID." ACCEPTED TRANSFER REQUEST OF AGENT #".$from_agent." ON TICKET #".$support_ticket_ID.". TRANSFERRED TO SELF.";
            } else {
                $sysGen = "SUP #".$processorID." ACCEPTED TRANSFER REQUEST OF AGENT #".$from_agent." ON TICKET #".$support_ticket_ID.". TRANSFERRED TO SUP #".$chosen_agent;
            }
        }

        $sysGenEXTERNAL = "SUP #".$processorID." PERFORMED AN EXTERNAL TRANSFER FOR AGENT #".$from_agent." ON TICKET #".$support_ticket_ID.". TRANSFERRED TO AGENT #".$chosen_agent;

        $sysGenEXTERNALFrom = ($transferToSup == 2 && $from_agent_sup != $processorID) ? $sysGenEXTERNAL : $sysGen;
        $sysGenEXTERNALChosen = ($transferToSup == 2 && $chosen_agent_sup != $processorID) ? $sysGenEXTERNAL : $sysGen;

        $permissionsIDFrom = ($transferToSup == 2 ) ? 1 : 3;
        $permissionsIDChosen = ($transferToSup == 2) ? 1 : 3;
        

        $result = "";

        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':notifID', $notification_ID);
            $stmt->bindparam(':supportTicketID', $support_ticket_ID);
            $stmt->bindparam(':supportTicketID2', $support_ticket_ID);
            $stmt->bindparam(':newAgent', $chosen_agent);
            $stmt->bindparam(':newAgent2', $chosen_agent);
            $stmt->bindparam(':pastAgent', $from_agent);
            $stmt->bindparam(':transferReasonID', $transfer_reason_id);
            $stmt->bindparam(':sysgen', $sysGen);
            $stmt->bindparam(':supportTicketID3', $support_ticket_ID);

             // Send Notification to Manager on an External Transfer
            if($externalTransfer == true && $processor_supID != null){
                $stmt->bindparam(':notifRecipient', $processor_supID);
                $stmt->bindparam(':supportTicketForNotif', $support_ticket_ID);
                $stmt->bindparam(':notifCreator', $processorID);
                $stmt->bindparam(':permissionsOwner', $processorID);
                $stmt->bindparam(':sysGenEXTERNAL', $sysGenEXTERNAL);
            }

             // Send Notification if From agent is not my agent
            if($from_agent_sup != null && $from_agent_sup != $processorID){
                $stmt->bindparam(':notifRecipientFrom', $from_agent_sup);
                $stmt->bindparam(':supportTicketForNotifFrom', $support_ticket_ID);
                $stmt->bindparam(':notifCreatorFrom', $processorID);
                $stmt->bindparam(':permissionIDFrom', $permissionsIDFrom);
                $stmt->bindparam(':permissionsOwnerFrom', $processorID);
                $stmt->bindparam(':sysGenEXTERNALFrom', $sysGenEXTERNALFrom);
            }

            // Send Notification if Chosen agent is not my agent
            // No need to send another notification if from agent sup is the same as chosen agent sup 
            // No need to send a notification to my sup (manager) if I am transfering the ticket to myself since external will always be flagged
            if($chosen_agent_sup != null && $chosen_agent_sup != $processorID && ($from_agent_sup != $chosen_agent_sup) && ($chosen_agent != $processor_supID)){
                $stmt->bindparam(':notifRecipientChosen', $chosen_agent_sup);
                $stmt->bindparam(':supportTicketForNotifChosen', $support_ticket_ID);
                $stmt->bindparam(':notifCreatorChosen', $processorID);
                $stmt->bindparam(':permissionIDChosen', $permissionsIDChosen);
                $stmt->bindparam(':permissionsOwnerChosen', $processorID);
                $stmt->bindparam(':sysGenEXTERNALChosen', $sysGenEXTERNALChosen);
            }

            $result = $stmt->execute();
        }

        $stmt=null;
        $conn=null;
        $db=null;

        $ModelResponse =  array(
            "success" => true,
            "data" => $result,
            // "sql" => $sql
        );

        return $ModelResponse;

        // Insert into support notifications
        // INSERT INTO `support_notifications` 
        // (`id`, `ticket_actions_id`, `recipient_id`, `support_ticket_id`, `notification_type_id`, 
        // `generated_by`, `permissions_id`, `permissions_owner`, `system_generated_description`, 
        // `has_taken_action`, `is_deleted`, `is_read`, `created_on`) 
        // VALUES 
        // (NULL, LAST_INSERT_ID(),:supID, :supTicketID, :notifType, 
        // :userID, :permissionsID, :permissionsOwner, :sysGen, 
        // '0', '0', '0', now());
    } catch (\PDOException $e) {

        $ModelResponse =  array(
            "success" => false,
            "data" => $e->getMessage()
        );
    }}



// =======================================================================

public function declineTransferRequest($notification_ID, $supportTicket_ID, $comment,  $sup_ID, $transfer_type_ID, $agent_ID)
    { 
        try {

            $db = new DB();
            $conn = $db->connect();

            $result = [];

            // Update notification
            // Insert into actions
            $sql = "SET @@session.time_zone = '+08:00'; BEGIN;

            UPDATE support_notifications sn SET sn.is_read = 1, sn.has_taken_action = 1 WHERE sn.id = :notifID;

            INSERT INTO ticket_actions 
            (action_taken, system_generated_description, agent_notes, support_ticket) 
            VALUES (13, :sysgen , NULL, :suportTicketID);
            
            COMMIT;";

            // :actionTaken = 13 - Transfer Denied
            $transferTypeText = $transfer_type_ID == 3 ? "ESCALATION" : "TRANSFER";
            $sysgen = "SUP #".$sup_ID." DENIED THE ".$transferTypeText." REQUEST OF AGENT #".$agent_ID." WITH REASON: ".$comment;
 
            $result = "";

            // Prepare statement
            $stmt =  $conn->prepare($sql); 
    
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':notifID', $notification_ID);
                $stmt->bindparam(':sysgen', $sysgen);
                $stmt->bindparam(':suportTicketID', $supportTicket_ID);
                $result = $stmt->execute();
            }
    
            $stmt=null;
            $conn=null;
            $db=null;;

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


// =======================================================================

  public function toggleReadNotif($notif_ID)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $current = "";
            $result = "";
            $is_read = null;


            // get current 
            $sqlCurrent = "SELECT sn.is_read FROM support_notifications sn WHERE sn.id = :notifID;";

                // Prepare statement
                $stmtCurrent =  $conn->prepare($sqlCurrent); 

                // Only fetch if prepare succeeded
                if ($stmtCurrent !== false) {
                    $stmtCurrent->bindparam(':notifID', $notif_ID);
                    $stmtCurrent->execute();
                    $current  = $stmtCurrent->fetch(\PDO::FETCH_ASSOC);
                }

                if( $current  != false){
                    $is_read = $current['is_read'];
                }

                $newVal = $is_read == 0 ? 1 : 0;

            // // save reverse
            $sql = "UPDATE support_notifications sn SET sn.is_read = :newVal WHERE sn.id = :notificationID;";

                // Prepare statement
                $stmt =  $conn->prepare($sql); 
        
                // Only fetch if prepare succeeded
                if ($stmt !== false) {
                    $stmt->bindparam(':notificationID', $notif_ID);
                    $stmt->bindparam(':newVal', $newVal);
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




public function check_pending_request($id_ticket)
    {
        try {

            $db = new DB();
            $conn = $db->connect();

            $sql = "SELECT * 
            FROM support_notifications sn 
            WHERE sn.support_ticket_id = :idTicket 
            AND sn.is_deleted = 0
            AND sn.has_taken_action = 0
            AND sn.notification_type_id = 2;";

            // Prepare statement
            $stmt =  $conn->prepare($sql); 
    
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':idTicket', $id_ticket);
                $result = $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
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

public function checkTime_last_notifyManager($managerID, $supID){
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

        $sysgen = 'SUP #'.$supID.' REQUESTS FOR MANAGER APPROVAL CODE RESET FROM MANAGER #'.$managerID.' ON '.$now ;

        $sql="SELECT sn.created_on 
        FROM support_notifications sn 
        WHERE 
        sn.generated_by = :createdBy
        AND sn.permissions_owner = :permissionsOwner
        AND sn.recipient_id = :recipientID
        AND sn.permissions_id = 1
        AND sn.notification_type_id = 5
        ORDER BY sn.created_on DESC
        LIMIT 1;";

        // Prepare statement
        $stmt =  $conn->prepare($sql); 

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':recipientID', $managerID);
            $stmt->bindparam(':createdBy', $supID);
            $stmt->bindparam(':permissionsOwner', $supID);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
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



  public function notifyManager($managerID, $supID)
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

            $sysgen = 'SUP #'.$supID.' REQUESTS FOR MANAGER APPROVAL CODE RESET FROM MANAGER #'.$managerID.' ON '.$now ;

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
                $stmt->bindparam(':recipientID', $managerID);
                $stmt->bindparam(':createdBy', $supID);
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
        // }}


}