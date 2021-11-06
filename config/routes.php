<?php

// Sample Routes

$app->get("/","GuestEntryController:loadApp");

$app->get("/create-guest","GuestEntryController:createGuest");

$app->get("/is_in_db","GuestEntryController:isInDb)");

$app->get("/view-guests" ,"GuestEntryController:viewGuests");

$app->get("/get-single-guest","GuestEntryController:getSingleGuest");

$app->get("/get-single-guest/{id}","GuestEntryController:getSingleGuest");

$app->get("/edit-single-guest/{id}","GuestEntryController:editGuest");

$app->get("/delete-guest/{id}","GuestEntryController:deleteGuest");

$app->get("/count-guests" ,"GuestEntryController:countGuests");


$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:Login");
    $app->post("/register","AuthController:Register");
});