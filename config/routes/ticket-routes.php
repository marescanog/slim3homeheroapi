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















});
