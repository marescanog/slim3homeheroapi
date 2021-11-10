<?php

$app->get("/create-guest","UserController:get_all_users");


$app->get("/user/check-number","UserController:is_in_DB");

$app->post("/user/register","UserController:register_user");




