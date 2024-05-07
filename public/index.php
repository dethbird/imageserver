<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
define("APPLICATION_PATH", __DIR__ . "/../");
date_default_timezone_set('America/New_York');

# Ensure src/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            APPLICATION_PATH,
            APPLICATION_PATH . 'library',
            get_include_path(),
        )
    )
);
define("CACHE_DIR", __DIR__ . "/cache/");


require '../vendor/autoload.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Create app
$app = AppFactory::create();

# all GET routes
$app->get('/', function (Request $request, Response $response, $args) {

    $queryParams = $request->getQueryParams();
    if (!isset ($queryParams['img'])) {
        $response->getBody()->write(json_encode(['error' => 'no img param']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }

    $width = isset ($queryParams['w']) ? $queryParams['w'] : 1920;
    $hash = md5($request->getUri()->getQuery());
    $extension = pathinfo($queryParams['img'], PATHINFO_EXTENSION);
    $filename = $hash . '.' . $extension;
    $imageData = null;

    if (file_exists(CACHE_DIR . $filename)) {
        $imageData = file_get_contents(CACHE_DIR . $filename);
    } else {
        # get image from url
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $queryParams['img']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
        $imageData = curl_exec($ch);
        curl_close($ch);

        $im = imagecreatefromstring($imageData);
        $resized = imagescale($im, $width);

        imagejpeg($resized, CACHE_DIR . $filename);
        $imageData = file_get_contents(CACHE_DIR . $filename);

    }

    $response->getBody()->write($imageData);
    return $response->withHeader('Content-Type', 'image/' . $extension);
})->setName('index');

$app->run();
