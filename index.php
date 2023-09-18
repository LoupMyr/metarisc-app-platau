<?php

require_once __DIR__.'/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use App\Controller\ConnectionController;
use App\Controller\AccessController;

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$responseFactory = new Laminas\Diactoros\ResponseFactory();

$router   = new League\Route\Router;

$loader = new Twig\Loader\FilesystemLoader(__DIR__.'/templates');
$twig = new Twig\Environment($loader, [
     'cache' => false,
]);

// Définition des routes
$router->map('GET', '/', function (ServerRequestInterface $request) use ($twig): ResponseInterface {
    $template = $twig->load('index.twig');
    $html = $template->render([]);
    $response = new Response();
    $response->getBody()->write($html);
    return $response;
});
$router->get('/connection', [new ConnectionController, 'connection']);
$router->get('/access', [new AccessController, 'access']);

$response = $router->dispatch($request);

// send the response to the browser
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
