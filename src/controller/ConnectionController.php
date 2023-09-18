<?php

namespace App\Controller;

use Twig;
use Laminas;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConnectionController
{
  public function connection(ServerRequestInterface $request, array $args): ResponseInterface {
    // Chargement du template de la page
    $loader = new Twig\Loader\FilesystemLoader(__DIR__.'/../../templates');
    $twig = new Twig\Environment($loader, [
      'cache' => false,
    ]);
    $template = $twig->load('connected.twig');

    // Metarisc call to get auth url
    $auth_url = \Metarisc\Metarisc::authorizeUrl([
      'client_id' => 'integration-platau-dev',
      'redirect_uri' => 'http://localhost:8000/access',
      'scope' => 'openid profile email',
    ]);
    $html = $template->render([
      "link" => $auth_url
    ]);

    $responseFactory = new Laminas\Diactoros\ResponseFactory();
    $response = $responseFactory->createResponse();

    $response->getBody()->write($html);
    return $response;
  }
}
