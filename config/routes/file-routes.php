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
    $app->post("/cancel-order2/{id}", "FileController:cancelJobOrder2"); //ver 2 - no token :(
    
    // When a job order has expired and the worker did not start, this route allows the user to cancel the job order and repost another one
    $app->post("/cancel-repost-order/{id}", "FileController:cancelRepostOrder");

    // Check if a job order already has a support ticket. Returns Suppport ticket info if true and false if none
    $app->get("/has-job-issue/{id}", "FileController:hasJobIssue");
    $app->get("/has-job-issue/{id}/{userid}", "FileController:hasJobIssue"); //ver 2 - no token :(

    // When a job order has expired and the worker did not start, this route allows the user to cancel the job order and repost another one
    // type - 1 (REPORT WORKER)
    // type - 2 (REPORT JOB ISSUE)
    $app->post("/report-job-issue/{type}/{id}", "FileController:reportJobIssue");
    $app->post("/report-job-issue2/{type}/{id}", "FileController:reportJobIssue2"); //ver 2 - no token :(

    // This updates the schedule of an existing POST
    $app->post("/update-schedule/{id}","FileController:updateSchedule");

    // This updates bill to confirm paid
    $app->post("/confirm-payment/{orderid}","FileController:confirmPayment");

    // This creates a new rating for a job order
    $app->post("/save-rating/{orderid}","FileController:saveRating");

    // This checks if a billing issue has already been filed, returns false if it does not and the billing & support ticket information if it has
    $app->get("/has-billing-issue/{orderid}","FileController:hasBillingIssue");
    $app->get("/has-billing-issue/{orderid}/{userid}","FileController:hasBillingIssue"); //ver 2 - no token :(

    // This creates a support ticket for the billing issue
    $app->post("/report-billing-issue/{orderid}","FileController:createBillingIssue");
    $app->post("/report-billing-issue2/{orderid}","FileController:createBillingIssue2"); //ver 2 - no token :(



    // Get Account Summary
    $app->get("/get-account-summary","FileController:getAccountSummary");

    // Get Address Info
    $app->get("/populate-edit-address/{homeid}","FileController:getFormForEditAddress");

    // Update Address Info
    $app->post("/update-address/{homeid}","FileController:updateAddress");

    // Update Address Info
    $app->post("/delete-address/{homeid}","FileController:deleteAddress");

    // Update Name
    $app->post("/profile-update-name","FileController:updateName");

    // Save profile pic location
    $app->post("/save-profile-pic-location","FileController:saveProfilePicLocation");

    // Change password
    $app->post("/change-password","FileController:changePassword");

    // VERIFY phone number before changing
    $app->post("/change-phone-verify","FileController:changePhoneVerify");

    // Update phone number (Verification steps have passed)
    $app->post("/update-phone-number","FileController:updatePhoneNumber");

    // Get All homeheroes who are active
    $app->get("/get-homeheroes","FileController:getHomeheroes");

    // Get All the projects of the user
    $app->get("/get-my-projects","FileController:getUsersProjects");

    // Send the project to the worker
    $app->post("/send-project/{workerID}","FileController:sendProjectToWorker");
});

// Get All cities
$app->get("/get-service-areas","FileController:getServiceAreas");

// Get All project types
$app->get("/get-project-types","FileController:getProjectTypes");
