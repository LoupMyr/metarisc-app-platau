<?php

namespace App\Http\Controller;

use Laminas;
use Twig\Environment;
use Metarisc\Metarisc;
use App\Service\SessionService;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutController
{
    public function __construct(
        private Environment $twig,
        private CacheInterface $cache,
        private SessionService $sessionService
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        if ($this->sessionService->isConnected()) {
            $this->sessionService->destroySession($this->cache);
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, $value, time() - 3600);
            }
        }

        $template = $this->twig->load('logout.twig');
        $html = $template->render();

        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();
        $response->getBody()->write($html);

        return $response;
    }
}