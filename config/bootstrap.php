<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use App\Response\CustomResponse;

use Slim\App;

require_once __DIR__. "/../vendor/autoload.php";

$settings = require_once  __DIR__ ."/settings.php";

$app = new App($settings);

$container = $app->getContainer();

// require_once __DIR__. '/errHandler.php';

$routeContainers = require_once __DIR__. '/routecontainers.php';

$routeContainers($container);

// Required Routes

require_once __DIR__. '/routes/user-routes.php';

$middleware = require_once __DIR__."/middleware.php";

$middleware($app);

$app->get('/', function (Request $request, Response $response) {
    $responseMessage = "Api for the homehero app";
    $customResponse = new CustomResponse();
    $customResponse->is200Response($response, $responseMessage);
});


$app->run();
