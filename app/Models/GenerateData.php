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


    public function getnewRegistrationTickets($total = null, $start = 60){
        try{
            $db = new DB();
            $conn = $db->connect();

            $sql = "SELECT * FROM `support_ticket` st 
                    WHERE st.id >= :startID 
                    AND st.issue_id = 1
                    AND st.status = 1
                    AND st.is_Archived = 0
                    ORDER BY st.created_on ASC";
            
            if($total == null){
                $sql =  $sql.";";
            } else {
                $sql =  $sql." LIMIT ".$total.";";
            }

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded //$id, $date,
            if ($stmt !== false) {
                $stmt->bindparam(':startID', $start);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            $stmt=null;
            $db=null;

            return array(
                "success"=>true,
                "data"=>$result
            );

        } catch (\PDOException $e) {
            return  array(
                "success"=>false,
                "data"=>$e->getMessage()
            );
        }
    }


    public function getAgetnListBasedOnTicketCreation($date, $role){
        try{
            $db = new DB();
            $conn = $db->connect();
            $result = "";
            $sql = "SELECT * FROM `support_agent` sa 
            LEFT JOIN hh_user hh ON sa.id = hh.user_id
            WHERE sa.created_on < :ticketCreationdate 
            AND sa.role_type = :sarole
            AND sa.is_deleted = 0
            AND hh.user_status_id = 2;";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded //$id, $date,
            if ($stmt !== false) {
                $stmt->bindparam(':ticketCreationdate', $date);
                $stmt->bindparam(':sarole', $role);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            $stmt=null;
            $db=null;

            return array(
                "success"=>true,
                "data"=>$result
            );

        } catch (\PDOException $e) {
            return array(
                "success"=>false,
                "data"=>$e->getMessage()
            );
        }
    }


// @name    assigns a ticket with an agent ID, adds a ticket action and a new ticket assignment into table
// @params  id
// @returns a Model Response object with the attributes "success" and "data"
//          sucess value is true when PDO is successful and false on failure
//          data value is
public function assign_ticket($date,$userID,$ticketID,$actionID=2,$description="",$stat=2,$newAgent=null,$prevAgent=null,$transferReason=null){
    try{
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $description=($description==""||$description==null)?"AGENT #".$userID." ACCEPTED TICKET":$description;

        $sql = "SET @@session.time_zone = '+08:00';
        BEGIN;
            UPDATE support_ticket s
            SET s.status = :stat, s.assigned_agent = :userID, s.last_updated_on = :adate, s.assigned_on = :adate2
            WHERE s.id = :ticketID;

            INSERT INTO ticket_actions(action_taken, system_generated_description, action_date, support_ticket) VALUES (:actionID,:description,:adate3,:sticketID);

            INSERT INTO ticket_assignment(support_ticket, date_assigned, newly_assigned_agent, previous_agent, transfer_reason) VALUES (:sticketID2, :adate4, :newAgent,:prevAgent,:reason);
        COMMIT;
        ";

        $new =  $newAgent==null?$userID:$newAgent;

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";
        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':userID', $userID);
            $stmt->bindparam(':adate', $date);
            $stmt->bindparam(':adate2', $date);
            $stmt->bindparam(':stat', $stat);
            $stmt->bindparam(':ticketID', $ticketID);
            $stmt->bindparam(':actionID', $actionID);
            $stmt->bindparam(':description', $description);
            $stmt->bindparam(':adate3', $date);
            $stmt->bindparam(':sticketID', $ticketID);
            $stmt->bindparam(':sticketID2', $ticketID);
            $stmt->bindparam(':adate4', $date);
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


//($newDate, $ticketID,  $otherAgentID , $otherAgentCxNotes, 1);
public function commentTicket($date, $ticketID, $workerID, $comment, $notify = null){

    /*  DEFINITION FOR REFERENCE

        has_AuthorTakenAction = 0 -> Agent is processing ticket

        has_AuthorTakenAction = 1 -> New ticket/No action taken yet

        has_AuthorTakenAction = 2 -> Agent requested Cx follow up

        has_AuthorTakenAction = 3 -> Cx requested agent follow up

        has_AuthorTakenAction = 4 -> Closed/Resolved ticket
    */
    try{
        $systemGenMessage = 'AGENT #'.$workerID.' ADDED NOTES TO TICKET #'.$ticketID;
        $db = new DB();
        $conn = $db->connect();

        // CREATE query
        $sql = "";

        $sql = "SET @@session.time_zone = '+08:00';
            BEGIN;
            UPDATE support_ticket st SET st.last_updated_on = :cdate, st.has_AuthorTakenAction = 0 WHERE st.id = :ticketID;
            INSERT INTO ticket_actions (action_taken, system_generated_description, agent_notes, action_date, support_ticket)
            VALUES (8, :sysMessage , :comment, :cdate2, :id);
            COMMIT;
        ";

        if($notify != null){
            $systemGenMessage = 'AGENT #'.$workerID.' REQUESTED CUSTOMER FOLLOW UP FOR TICKET #'.$ticketID;
            $sql = "SET @@session.time_zone = '+08:00';
                BEGIN;
                    UPDATE support_ticket st SET st.last_updated_on = :cdate, st.has_AuthorTakenAction = :interaction WHERE st.id = :ticketID;
                    INSERT INTO ticket_actions (action_taken, system_generated_description, agent_notes,action_date, support_ticket)
                    VALUES (:action, :sysMessage , :comment, :cdate2, :id);
                COMMIT;
            ";
        }

        // Prepare statement
        $stmt =  $conn->prepare($sql);
        $result = "";

        // Only fetch if prepare succeeded
        if ($stmt !== false) {
            $stmt->bindparam(':cdate', $date);
            $stmt->bindparam(':id', $ticketID);
            $stmt->bindparam(':comment', $comment);
            $stmt->bindparam(':sysMessage', $systemGenMessage);
            $stmt->bindparam(':cdate2', $date);
            $stmt->bindparam(':ticketID', $ticketID);
            if($notify != null){
                $actioneye = $notify == 2 ? 9 : 14;
                $stmt->bindparam(':action', $actioneye);
                $stmt->bindparam(':interaction', $notify);
            }
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



public function updateTicketActionsDate($userID, $creationDate){
    try{
        $db = new DB();
        $conn = $db->connect();
        $result = "";
        $sql = "SELECT id FROM `support_ticket` st
        WHERE st.author = :userID
        AND st.issue_id = 1
        AND st.status = 1
        AND st.is_Archived = 0
        AND st. assigned_agent IS NULL
        ORDER BY st.created_on DESC;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // // Only fetch if prepare succeeded //$id, $date,
        if ($stmt !== false) {
            $stmt->bindparam(':userID', $userID);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if($result == false){
            return array(
                "success"=>false,
                "data"=>$result
            );
        }

        $id = $result['id'];

        $sql2 = "UPDATE `ticket_actions` SET `action_date` = :cdate 
        WHERE `ticket_actions`.`support_ticket` = :ticketID;";

        // Prepare statement
        $stmt2 =  $conn->prepare($sql2);

        // // Only fetch if prepare succeeded //$id, $date,
        if ( $stmt2 !== false) {
            $stmt2->bindparam(':ticketID', $id );
            $stmt2->bindparam(':cdate', $creationDate);
            $result =   $stmt2->execute();
        }

        $stmt=null;
        $stmt2=null;
        $db=null;

        return array(
            "success"=>true,
            "data"=>$result
        );

    } catch (\PDOException $e) {
        return array(
            "success"=>false,
            "data"=>$e->getMessage()
        );
    }
}



public function updateNBIDate($userID, $creationDate){
    try{
        $db = new DB();
        $conn = $db->connect();
        $result = "";
        $sql = "SELECT id 
        FROM nbi_information ni 
        WHERE ni.worker_id = :userID
        AND ni.is_deleted = 0
        AND ni.is_verified = 0
        ORDER BY ni.created_on DESC;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // // Only fetch if prepare succeeded //$id, $date,
        if ($stmt !== false) {
            $stmt->bindparam(':userID', $userID);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if($result == false){
            return array(
                "success"=>false,
                "data"=>$result
            );
        }

        $id = $result['id'];

        $sql2 = "UPDATE nbi_information ni SET ni.created_on = :cdate WHERE ni.id = :nbiID;";

        // Prepare statement
        $stmt2 =  $conn->prepare($sql2);

        // // Only fetch if prepare succeeded //$id, $date,
        if ( $stmt2 !== false) {
            $stmt2->bindparam(':nbiID', $id );
            $stmt2->bindparam(':cdate', $creationDate);
            $result =   $stmt2->execute();
        }

        $stmt=null;
        $stmt2=null;
        $db=null;

        return array(
            "success"=>true,
            "data"=>$result
        );

    } catch (\PDOException $e) {
        return array(
            "success"=>false,
            "data"=>$e->getMessage()
        );
    }
}



public function getNBIInfo($supportTicketID){
    try{
        $db = new DB();
        $conn = $db->connect();
        $result = "";
        $sql = "SELECT ni.id FROM `nbi_information` ni WHERE ni.support_ticket = :supTicketID;";

        // Prepare statement
        $stmt =  $conn->prepare($sql);

        // Only fetch if prepare succeeded //$id, $date,
        if ($stmt !== false) {
            $stmt->bindparam(':supTicketID', $supportTicketID);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $stmt=null;
        $db=null;

        return array(
            "success"=>true,
            "data"=>$result
        );

    } catch (\PDOException $e) {
        return array(
            "success"=>false,
            "data"=>$e->getMessage()
        );
    }
}





    public function update_worker_registration($newDate, $creationDate, $agentID, $ticketID, $workerID, $nbiID, $option, $comment = null){
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
                UPDATE nbi_information ni SET ni.is_verified = :verifyNum, ni.created_on = :createdate WHERE ni.id = :nbiID;
    
                UPDATE hh_user hh SET hh.user_status_id = :verifyUser WHERE hh.user_id = :workerID;
    
                UPDATE support_ticket st SET st.status = 4, st.last_updated_on = :newDate, st.has_AuthorTakenAction = 4 WHERE st.id = :ticketID;
    
                INSERT INTO ticket_actions(action_taken, system_generated_description,agent_notes, action_date, support_ticket) VALUES (7,:description,:notes, :newDate2,:sticketID);
            COMMIT;
            ";
    
            // Prepare statement
            $stmt =  $conn->prepare($sql);
            $result = "";
    
            // Only fetch if prepare succeeded
            if ($stmt !== false) {
                $stmt->bindparam(':nbiID', $nbiID);
                $stmt->bindparam(':verifyNum', $verifyNum);
                $stmt->bindparam(':createdate', $creationDate);
                $stmt->bindparam(':workerID', $workerID);
                $stmt->bindparam(':newDate', $newDate);
                $stmt->bindparam(':verifyUser', $verifyUser);
                $stmt->bindparam(':ticketID', $ticketID);
                $stmt->bindparam(':description', $sysDes );
                $stmt->bindparam(':notes', $comment);
                $stmt->bindparam(':newDate2',$newDate);
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




    public function getListHomeownersByCreationDate($date, $direction = 2){
        try{
            $db = new DB();
            $conn = $db->connect();
            $result = "";

            
            $symbol = $direction == 1 ? '<=': '>=';
            $sql = "SELECT * FROM `hh_user` hh 
            WHERE hh.user_type_id = 1 
            AND hh.user_status_id = 2
            AND hh.created_on ".$symbol." :cdate
            ORDER BY hh.created_on ASC";

            // Prepare statement
            $stmt =  $conn->prepare($sql);

            // Only fetch if prepare succeeded 
            if ($stmt !== false) {
                $stmt->bindparam(':cdate', $date);
                $stmt->execute();
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            $stmt=null;
            $db=null;

            return array(
                "success"=>true,
                "data"=>$result
            );

        } catch (\PDOException $e) {
            return array(
                "success"=>false,
                "data"=>$e->getMessage()
            );
        }
    }




public function saveProject(
        $createDate,
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

        $sql = "SET @@session.time_zone = '+08:00'; INSERT INTO job_post (homeowner_id, home_id, job_size_id, required_expertise_id, job_post_status_id, job_description, rate_offer, rate_type_id, is_exact_schedule, preferred_date_time, created_on, job_post_name)
                VALUES (:userID, :homeID, :jobSize, :expert, 1, :jobdesc, :rateoffer, :ratetype, :isexact, :prefdateTime, :cdate, :jobPostName);";

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
            $stmt->bindparam(':cdate', $createDate);
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












































































































































































    public function test($id, $date, $userType){
        try{
            $db = new DB();
            $conn = $db->connect();
            $result = "";
            $sql = "";

            // // Prepare statement
            // $stmt =  $conn->prepare($sql);

            // // Only fetch if prepare succeeded //$id, $date,
            // if ($stmt !== false) {
            //     $stmt->bindparam(':startID', $start);
            //     $stmt->execute();
            //     $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            // }

            $stmt=null;
            $db=null;

            return array(
                "success"=>true,
                "data"=>$result
            );

        } catch (\PDOException $e) {
            return array(
                "success"=>false,
                "data"=>$e->getMessage()
            );
        }
    }

}