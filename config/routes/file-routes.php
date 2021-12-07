<?php

$app->post("/upload","FileController:upload");

$app->get("/search-proj","FileController:searchProj");

$app->get("/populate-address-form","FileController:populateAddressForm");

$app->post("/add-address","FileController:addAddress");

$app->post("/add-project","FileController:addProject");


$app->group("/homeowner",function() use ($app){
    
    // This route gets ongoing and current projects
    $app->get("/get-projects","FileController:getProjects");

    // This route includes job post, job order, job bill and review details.
    $app->get("/get-single-project-complete-info/{id}","FileController:getSingleProject");

    // This route gets all the user's addresses
    $app->get("/get-all-addresses", "FileController:getAllAddresses");

    // This route updates a user's job post
    $app->post("/update-post/{id}", "FileController:updateJobPost");

});

