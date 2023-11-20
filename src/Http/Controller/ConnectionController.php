<?php

namespace App\Http\Controller;

use Laminas;
use Twig\Environment;
use Metarisc\Metarisc;
use App\Service\SessionService;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Metarisc\Auth\OAuth2;

class ConnectionController
{
    public function __construct(
        private Environment $twig,
        private Metarisc $metarisc,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        // Création du lien de redirection vers Metarisc pour permettre à l'utilisateu de se connecter
        $auth_url = OAuth2::authorizeUrl([
            'client_id'     => 'integration-platau-dev',
            'redirect_uri'  => 'http://localhost:8000/access',
            'scope'         => 'openid profile email',
        ]);


        // Génération de la vue HTML
        $template = $this->twig->load('connected.twig');
        $html = $template->render([
            'link' => $auth_url,
        ]);

        // Création de la réponse HTTP
        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();
        $response->getBody()->write($html);

        return $response;
    }
}
