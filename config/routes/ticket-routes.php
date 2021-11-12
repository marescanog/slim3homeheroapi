<?php

$app->group("/ticket",function() use ($app){

    $app->post("/create","SupportTicketController:createTicket");

});
