<?php

namespace App\Http\Controller;

use Laminas;
use Twig\Environment;
use Metarisc\Metarisc;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConnectionController
{
    public function __construct(
        private Environment $twig,
        private Metarisc $metarisc
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        $template = $this->twig->load('connected.twig');

        $auth_url =$this->metarisc->authorizeUrl([
            'client_id'     => 'integration-platau-dev',
            'redirect_uri' => 'http://localhost:8000/access',
            'scope'        => 'openid profile email',
        ]);

        $html = $template->render([
          'link' => $auth_url,
        ]);

        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();

        $response->getBody()->write($html);

        return $response;
    }
}
