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

        // Recieves a phone number and checks if the user has completed registration
        // Case when worker is filling in registration pages and accidentally closes browser
        // he/she can still continue filling in registration pages
        $app->get("/hasRegistered", "AuthController:hasWorkerRegistered"); // Worker: 2nd Step, checking
    });


    $app->get("/check-phone", "AuthController:userPhoneCheck"); // Global
    $app->post("/verify-password", "AuthController:userVerifyPass"); // Global

    // DUMMY ROUTES
    $app->post("/generate-SMS-dummy", "AuthController:generateSMSDummy"); // Global
    $app->post("/verify-SMS-dummy", "AuthController:verifySMSDummy"); // Global
});



