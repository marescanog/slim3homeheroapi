<?php

$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:userLogin");
    
});




