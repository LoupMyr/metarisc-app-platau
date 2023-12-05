<?php

namespace App\Http\Strategy;

use Twig\Environment;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Http\Exception\NotFoundException;

class NotFoundStrategy extends ApplicationStrategy
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function getNotFoundDecorator(NotFoundException $exception) : MiddlewareInterface
    {
        return new class($exception, $this->twig) implements MiddlewareInterface {
            protected $exception;
            protected $twig;

            public function __construct(\Exception $exception, Environment $twig)
            {
                $this->exception = $exception;
                $this->twig      = $twig;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $template = $this->twig->load('error.twig');
                $html     = $template->render([
                    'error' => $this->exception->getMessage(),
                ]);

                // Création de la réponse HTTP
                $responseFactory = new ResponseFactory();
                $response        = $responseFactory->createResponse();
                $response->getBody()->write($html);

                return $response->withStatus(302);
            }
        };
    }
}
