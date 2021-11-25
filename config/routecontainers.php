<?php
return function($container)
{

  $container["AuthController"] = function()
    {
      return new \App\Controllers\AuthController;
    };

    $container["UserController"] = function()
    {
      return new \App\Controllers\UserController;
    };

    $container["SupportTicketController"] = function()
    {
      return new \App\Controllers\SupportTicketController;
    };

    $container["FileController"] = function()
    {
      return new \App\Controllers\FileController;
    };

};