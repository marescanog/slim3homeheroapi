<?php

$app->get("/test-support-agent","SupportAgentController:test");

$app->group("/support",function() use ($app){
    $app->post("/ticket-dashboard","SupportAgentController:getTicketDashboard");
    $app->post("/my-tickets","SupportAgentController:getMyTickets");
    $app->post("/get-my-codes","SupportAgentController:getMyCodes");
    $app->post("/get-sup-reason","SupportAgentController:getSupReason");
    $app->post("/get-account-details","SupportAgentController:getAccountDetails");
    $app->post("/get-team-details","SupportAgentController:getTeamDetails");
    $app->post("/add-anouncement","SupportAgentController:addAnouncement");
    $app->post("/delete-anouncement/{aid}","SupportAgentController:deleteAnouncement");
    $app->post("/get-single-anouncement/{aid}","SupportAgentController:getSingleAnouncement");
    $app->post("/edit-anouncement/{aid}","SupportAgentController:editAnouncement");
});