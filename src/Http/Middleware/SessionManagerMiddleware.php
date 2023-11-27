<?php

namespace App\Http\Middleware;

use App\Service\SessionService;
use Middlewares\PhpSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionManagerMiddleware extends PhpSession//implements MiddlewareInterface
{

    public function __construct()
     {
     }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $this->options([
            'cookie_secure'   => true,
            'cookie_httponly' => true,
            'use_cookies' => false,
            'use_only_cookies' => true,
            'cache_limiter' => ''
        ]);

        return parent::process($request, $handler);
    }


    /*public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        /*if (!$this->sessionService->isConnected()) {
            $this->sessionService->startSecureSession();
        }
        $this->

        return $handler->handle($request);
    }*/
}
