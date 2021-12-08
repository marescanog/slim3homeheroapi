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

    // This route updates a user's job post - TODO: ADD CHECK TO SEE IF POST BELONGS TO USER (REFER TO CANCEL)
    $app->post("/update-post/{id}", "FileController:updateJobPost");

    // This route cancels a user's job post through soft delete
    $app->post("/cancel-post/{id}", "FileController:cancelJobPost");

    // This route cancels a user's job order through soft delete
    $app->post("/cancel-order/{id}", "FileController:cancelJobOrder");
    
    // When a job order has expired and the worker did not start, this route allows the user to cancel the job order and repost another one
    $app->post("/cancel-repost-order/{id}", "FileController:cancelRepostOrder");

    // Check if a job order already has a support ticket. Returns Suppport ticket info if true and false if none
    $app->get("/has-job-issue/{id}", "FileController:hasJobIssue");

    // When a job order has expired and the worker did not start, this route allows the user to cancel the job order and repost another one
    // type - 1 (REPORT WORKER)
    // type - 2 (REPORT JOB ISSUE)
    $app->post("/report-job-issue/{type}/{id}", "FileController:reportJobIssue");

    // This updates the schedule of an existing POST
    $app->post("/update-schedule/{id}","FileController:updateSchedule");

    // This updates bill to confirm paid
    $app->post("/confirm-payment/{orderid}","FileController:confirmPayment");

    // This creates a new rating for a job order
    $app->post("/save-rating/{orderid}","FileController:saveRating");
});

