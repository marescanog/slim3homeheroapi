<?php

$app->group("/registration",function() use ($app){
    $app->get("/personal-info","WorkerController:getRegistration_personalInfo");
    $app->post("/save-personal-info","WorkerController:save_personal_info");

    $app->get("/general-schedule","WorkerController:get_general_schedule");
    $app->post("/save-general-schedule","WorkerController:save_general_schedule");
    $app->post("/save-specific-schedule","WorkerController:save_specific_schedule");

    $app->get("/preferred-cities","WorkerController:get_preferred_cities");
    $app->post("/save-preferred-cities","WorkerController:save_preferred_cities");

    $app->get("/review-information","WorkerController:get_registration_review");
    $app->post("/submit-application","WorkerController:submit_application");
});

$app->group("/worker",function() use ($app){
    $app->get("/job-postings/{id}","WorkerController:getJobPostings");
    $app->get("/ongoing-job-orders/{id}","WorkerController:getOngoingJobOrders");
    $app->get("/past-job-orders/{id}","WorkerController:getPastJobOrders");
    $app->get("/reviews/{id}","WorkerController:getReviews");
    // 2. Get Job Postings (Restrict by worker's preferred city & skillset)
    // 3. Get Ongoing Job Orders (Restrict by worker id/ only logged in workers postings)
    // 4. Get Past Job Orders (Restrict by worker id/ only logged in workers postings & isCompleted)
    //    - One version includes cancelled job orders
    //    - Another version only includes successfully billed job orders
    // 5. Get Reviews (Restrict by worker id/ only logged in workers info)
    // 6. PUT Update NBI Info - Will be handled by Worker registration Route (So just use reuse the route used in registration)
    // 7. POST add Licesce & Certificate
    // 8. PUT/POST add Introduction
    // 9. PUT update information - uses a combination of functions from models
    // 10. PUT save featured projects
    // 11. PUT/POST add project photos (two routes are needed, one route is saving to the google cloud storage- currently there's only 1 route for save one photo and not multiple photos. The multiple photos is still pending, the other route will be your code to save information to DB)
    // 12. Get Worker Info  - For the account profile page, feel free to use the DB functions in the model or write your own
    // 13. Get/Save Services offered from DB  - Worker ( refer to Project type table and not expertise)
    
});