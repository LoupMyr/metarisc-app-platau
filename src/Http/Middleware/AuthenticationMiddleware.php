<?php

namespace App\Http\Middleware;

use Metarisc\Metarisc;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheInterface $cache,
        private Metarisc $metarisc
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->metarisc->authenticate('oauth2:client_credentials', [
            'scope'        => 'openid profile email',
        ], $this->cache);

        return $handler->handle($request);
    }
}
