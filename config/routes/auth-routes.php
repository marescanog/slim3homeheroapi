<?php

$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:userLogin"); // This function will soon be rewritten and depreciated Refer to New Routes instead
    $app->post("/user-registration","AuthController:userRegister"); // This function will soon be rewritten and depreciated Refer to New Routes instead
    $app->post("/support-login","AuthController:supportRegister");

    // Returns a JWT token with additional Login info after password confirmation
    $app->post("/create-login-token", "AuthController:createLoginToken");
    $app->post("/decode-token", "AuthController:decodeLoginToken");

    // Universal Login, instead of returning a JWT, it returns users associated with phone number
    $app->post("/client-login","AuthController:login"); 

// New routes
$app->group("/homeowner",function() use ($app){
    // Creates a hh_user entry, worker entry & schedule entry in the DB
    // Pre-verified by /auth/check-phone and /auth/verify-password
    $app->post("/create-account", "AuthController:homeownerCreateAccount"); 
});

// New routes
$app->group("/worker",function() use ($app){
        // Creates a hh_user entry, worker entry & schedule entry in the DB
        // Pre-verified by /auth/check-phone and /auth/verify-password
        $app->post("/create-account", "AuthController:workerCreateAccount"); // Worker: 1st Step

        // Recieves a phone number and checks if the user has completed registration
        // Case when worker is filling in registration pages and accidentally closes browser
        // he/she can still continue filling in registration pages
        $app->get("/hasRegistered", "AuthController:hasWorkerRegistered"); // Worker: 2nd Step, checking

        // Returns a JWT token with additional registration info after password confirmation
        $app->post("/create-registration-token", "AuthController:createRegistrationToken");
    });


    $app->get("/check-phone", "AuthController:userPhoneCheck"); // Global, checks if phone is in DB
    $app->post("/verify-password", "AuthController:userVerifyPass"); // Global, verify if passwords match. Returns hashed pass

    // DUMMY ROUTES
    $app->post("/generate-SMS-dummy", "AuthController:generateSMSDummy"); // Global, for SMS PIN
    $app->post("/verify-SMS-dummy", "AuthController:verifySMSDummy"); // Global, for SMS PIN

});

// New routes Apr 14
$app->group("/support",function() use ($app){
    $app->post("/login", "AuthController:supportlogin");  //change to post
    $app->post("/generate-permissions", "AuthController:generatePermission");  //change to post
});




// New routes June 6 
$app->group("/generate-data",function() use ($app){
    $app->post("/test", "GenerateDataController:test");  //change to post
    $app->post("/generate-homeowners", "GenerateDataController:generateHomeOwners"); 
    $app->post("/change-user-create-date", "GenerateDataController:changeUserCreateDate"); 
    $app->post("/add-homes-to-homeowners", "GenerateDataController:addHomesToHomeowners"); 
    $app->post("/generate-workers", "GenerateDataController:generateWorkers"); 
    $app->post("/complete-worker-registration", "GenerateDataController:completeWorkerRegistration");
    $app->post("/generate-support-agents", "GenerateDataController:generateSupportAgents");  
    $app->post("/approve-worker", "GenerateDataController:approveWorker");  
});
