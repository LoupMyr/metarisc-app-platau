<?php

namespace App\Http\Middleware;

use Twig\Environment;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\Route\Http\Exception\NotFoundException;

class NotFoundMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            // Essayez de gérer la requête
            return $handler->handle($request);
        } catch (NotFoundException $exception) {
            $template = $this->twig->load('error.twig');
            $html     = $template->render([
                'error' => $exception->getMessage(),
            ]);

            // Création de la réponse HTTP
            $responseFactory = new ResponseFactory();
            $response        = $responseFactory->createResponse();
            $response->getBody()->write($html);

            return $response->withStatus(302);
        }
    }
}
