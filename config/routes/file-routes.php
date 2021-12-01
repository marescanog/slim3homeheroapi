<?php

$app->post("/upload","FileController:upload");

$app->get("/search-proj","FileController:searchProj");


$app->get("/populate-address-form","FileController:populateAddressForm");

$app->post("/add-address","FileController:addAddress");

$app->post("/add-project","FileController:addProject");

