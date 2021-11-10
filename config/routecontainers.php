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

};