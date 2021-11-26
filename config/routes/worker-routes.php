<?php

$app->group("/registration",function() use ($app){
    $app->get("/personal-info","WorkerController:getRegistration_personalInfo");
});
