<?php

namespace App\Controller;

use Twig;
use Laminas;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessController
{
  public function access(ServerRequestInterface $request, array $args): ResponseInterface {
    // Chargement du template de la page
    $loader = new Twig\Loader\FilesystemLoader(__DIR__.'/../../templates');
    $twig = new Twig\Environment($loader, [
      'cache' => false,
    ]);
    $template = $twig->load('home.twig');

    $session_state = $_GET["session_state"];
    $code = $_GET["code"];

    $metarisc = new \Metarisc\Metarisc([
      'metarisc_url' => 'https://api.metarisc.fr',
      'grant_type' => 'code',
      'code' => $code,
      'redirect_uri' => 'http://localhost:8000/access',
      'client_id' => 'integration-platau-dev',
    ]);

    $profil = $metarisc->request('GET', '/utilisateurs/@moi')->getBody()->__toString();
    $profil_json = json_decode($profil, true);

    $html = $template->render([
      'user_profile' => $profil_json,
    ]);

    $responseFactory = new Laminas\Diactoros\ResponseFactory();
    $response = $responseFactory->createResponse();

    $response->getBody()->write($html);
    return $response;
  }
}