<?php

$app->get("/create-guest","UserController:get_all_users");


$app->get("/user/check-number","UserController:is_in_DB");

$app->get("/get-single-user/{id}","UserController:get_single_user");

$app->get("/get-single-user-by-phone/{phone}","UserController:get_single_user");





