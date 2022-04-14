<?php

$app->get("/test-support-agent","SupportAgentController:test");

$app->group("/support",function() use ($app){
    $app->post("/ticket-dashboard","SupportAgentController:getTicketDashboard");
});