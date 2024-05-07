<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors',1);
define("APPLICATION_PATH", __DIR__ . "/../");
date_default_timezone_set('America/New_York');

# Ensure src/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH ,
    APPLICATION_PATH . 'library',
    get_include_path(),
)));


require '../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Create app
$app = AppFactory::create();

# all GET routes
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode(['pizza' => 'party']));
    return $response->withHeader('Content-Type', 'application/json');
})->setName('index');

$app->run();
