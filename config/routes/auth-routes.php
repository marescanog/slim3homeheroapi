<?php

$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:userLogin");
    $app->post("/user-registration","AuthController:userRegister");
});




