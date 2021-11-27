<?php

$app->group("/registration",function() use ($app){
    $app->get("/personal-info","WorkerController:getRegistration_personalInfo");
    $app->post("/save-personal-info","WorkerController:save_personal_info");
});
