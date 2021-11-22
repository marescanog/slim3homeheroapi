<?php

$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:userLogin");
    $app->post("/user-registration","AuthController:userRegister");
    $app->post("/support-login","AuthController:supportRegister");

    $app->get("/check-phone", "AuthController:userPhoneCheck");
    $app->get("/verify-password", "AuthController:userVerifyPass");

    // DUMMY ROUTES
    $app->post("/generate-SMS-dummy", "AuthController:generateSMSDummy");
    $app->post("/verify-SMS-dummy", "AuthController:verifySMSDummy");
});



