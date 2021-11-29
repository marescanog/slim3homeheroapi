<?php

$app->group("/registration",function() use ($app){
    $app->get("/personal-info","WorkerController:getRegistration_personalInfo");
    $app->post("/save-personal-info","WorkerController:save_personal_info");

    $app->get("/general-schedule","WorkerController:get_general_schedule");
    $app->post("/save-general-schedule","WorkerController:save_general_schedule");

    $app->get("/preferred-cities","WorkerController:get_preferred_cities");
    $app->post("/save-preferred-cities","WorkerController:save_preferred_cities");

    $app->get("/review-information","WorkerController:get_registration_review");
});
