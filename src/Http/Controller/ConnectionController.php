<?php

namespace App\Http\Controller;

use Laminas;
use Twig\Environment;
use Metarisc\Metarisc;
use Metarisc\Auth\OAuth2;
use App\Service\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConnectionController
{
    public function __construct(
        private Environment $twig,
        private SessionService $sessionService
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        if (null !== $this->sessionService->getSessionCookiesToken()) {
            header('Location: http://localhost:8000/home');
            exit;
        }
        // Création du lien de redirection vers Metarisc pour permettre à l'utilisateu de se connecter
        $auth_url = OAuth2::authorizeUrl([
            'client_id'     => 'integration-platau-dev',
            'redirect_uri'  => 'http://localhost:8000/access',
            'scope'         => 'openid profile email',
        ]);

        // Génération de la vue HTML
        $template = $this->twig->load('connected.twig');
        $html     = $template->render([
            'link' => $auth_url,
        ]);

        // Création de la réponse HTTP
        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();
        $response->getBody()->write($html);

        return $response;
    }
}
