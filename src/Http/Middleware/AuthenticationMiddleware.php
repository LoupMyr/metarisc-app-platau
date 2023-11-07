<?php

namespace App\Http\Middleware;

use Metarisc\Metarisc;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Metarisc $metarisc
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->metarisc->authenticate('oauth2:client_credentials', [
            'scope' => 'openid profile email',
        ]);
        if ('/connection' != $request->getUri()->getPath() && '/' != $request->getUri()->getPath()) {
            try {
                // REQUETE POUR VOIR SI LE USER EST CONNECTE
                $response = $this->metarisc->request('GET', '/@moi', ['auth' => 'oauth']);
                // VERIFICATION QUE LES RESPONSES NE SONT OK (!=200)
                if (200 != $response->getStatusCode()) {
                    // throw new \Exception('Action non autorisé', 404);
                }
            } catch (\Exception $e) {
                throw new \Exception('Action non autorisé', 404);
            }
        }

        return $handler->handle($request);
    }
}
