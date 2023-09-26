<?php

namespace App\Http\Controller;

use Twig\Environment;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        $template = $this->twig->load('index.twig');

        $html     = $template->render([]);

        $response = new Response();
        $response->getBody()->write($html);

        return $response;
    }
}
