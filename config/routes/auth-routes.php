<?php

$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:userLogin");
    $app->post("/user-registration","AuthController:userRegister"); // This function will soon be rewritten and depreciated
    $app->post("/support-login","AuthController:supportRegister");

// New routes
    $app->group("/worker",function() use ($app){
        // Creates a hh_user entry, worker entry & schedule entry in the DB
        // Pre-verified by /auth/check-phone and /auth/verify-password
        $app->post("/create-account", "AuthController:workerCreateAccount"); // Worker: 1st Step
    });


    $app->get("/check-phone", "AuthController:userPhoneCheck"); // Global
    $app->get("/verify-password", "AuthController:userVerifyPass"); // Global, should be POST since password will update later

    // DUMMY ROUTES
    $app->post("/generate-SMS-dummy", "AuthController:generateSMSDummy"); // Global
    $app->post("/verify-SMS-dummy", "AuthController:verifySMSDummy"); // Global
});



