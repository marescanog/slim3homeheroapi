<?php

$app->get("/test-support-agent","SupportAgentController:test");

$app->group("/support",function() use ($app){
    $app->post("/ticket-dashboard","SupportAgentController:getTicketDashboard");
    $app->post("/my-tickets","SupportAgentController:getMyTickets");
    $app->post("/get-my-codes","SupportAgentController:getMyCodes");
    $app->post("/get-sup-reason","SupportAgentController:getSupReason");
    $app->post("/get-account-details","SupportAgentController:getAccountDetails");
});