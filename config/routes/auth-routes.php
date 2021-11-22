<?php

$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:userLogin");
    $app->post("/user-registration","AuthController:userRegister"); // This function will soon be rewritten and depreciated
    $app->post("/support-login","AuthController:supportRegister");

// New routes


    $app->get("/check-phone", "AuthController:userPhoneCheck"); // Global
    $app->get("/verify-password", "AuthController:userVerifyPass"); // Global, should be POST since password

    // DUMMY ROUTES
    $app->post("/generate-SMS-dummy", "AuthController:generateSMSDummy"); // Global
    $app->post("/verify-SMS-dummy", "AuthController:verifySMSDummy"); // Global
});



