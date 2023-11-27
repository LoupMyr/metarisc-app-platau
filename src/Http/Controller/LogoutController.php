<?php

namespace App\Http\Controller;

use Laminas;
use Twig\Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutController
{
    public function __construct(
        private Environment $twig,
        private Laminas\Session\SessionManager $sessionManager
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        if ($this->sessionManager->sessionExists()) {
            $this->sessionManager->getStorage()->clear();
            $this->sessionManager->expireSessionCookie();
            $this->sessionManager->destroy();
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, $value, time() - 3600);
            }
        }
        $template = $this->twig->load('logout.twig');
        $html     = $template->render();

        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();
        $response->getBody()->write($html);

        return $response;
    }
}
