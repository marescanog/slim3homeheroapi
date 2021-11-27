<?php

$app->group("/ticket",function() use ($app){

    $app->post("/create","SupportTicketController:createTicket");
    $app->get("/get-resolved","SupportTicketController:getResolved");
    $app->get("/get-resolved/{id}","SupportTicketController:getResolvedSingle");

});
