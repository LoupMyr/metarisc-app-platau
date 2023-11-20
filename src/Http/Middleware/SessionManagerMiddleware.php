<?php

namespace App\Http\Middleware;

use App\Service\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionManagerMiddleware implements MiddlewareInterface
{
    public function __construct(private SessionService $sessionService)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if(!$this->sessionService->isConnected()) {
            $this->sessionService->startSecureSession();
        }

        return $handler->handle($request);
    }
}
