<?php

$app->group("/ticket",function() use ($app){

    $app->post("/create","SupportTicketController:createTicket");


    $app->get("/get-all", "SupportTicketController:getAll");


    $app->get("/get-single/{id}", "SupportTicketController:getSingle");


    $app->get("/search/{limit}/{page}/{type}/{keywords}", "SupportTicketController:search");


    $app->post("/get-info/{id}", "SupportTicketController:getInfo");

    
    $app->post("/all","SupportTicketController:getAllTickets");

    
    $app->post("/assign/{id}","SupportTicketController:assignTicket");

// TODO
    $app->post("/add-comment/{id}","SupportTicketController:commentTicket");


    $app->post("/update-worker-register/{id}","SupportTicketController:updateWorkerRegistration");


    $app->post("/process-bill-issue/{id}","SupportTicketController:processBilling");


    $app->post("/get-homeowner-address/{id}","SupportTicketController:getAddressList");


    $app->post("/process-job-issue/{id}","SupportTicketController:processJobIssue");


    $app->post("/request-transfer/{ticketID}","SupportTicketController:requestTransfer");


    $app->post("/get-notifications","SupportTicketController:getNotifications");


    $app->post("/get-agents-applicable-for-transfer/{notifID}","SupportTicketController:getAgentsApplicableForTransfer");


    $app->post("/process-transfer/{notifID}","SupportTicketController:processTransfer");


   


});
