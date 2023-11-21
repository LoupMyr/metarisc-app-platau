<?php

namespace App\Http\Controller;

use Laminas;
use Twig\Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorAuthController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        if(!isset($request->getQueryParams()['error'])){
            throw new \Exception('AIE', 400);
        }
        $error = $request->getQueryParams()['error'];
        // Génération de la vue HTML
        $template = $this->twig->load('errorAuth.twig');
        $html     = $template->render([
           'error' => $error
        ]);

        // Création de la réponse HTTP
        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();
        $response->getBody()->write($html);

        return $response;
    }
}
