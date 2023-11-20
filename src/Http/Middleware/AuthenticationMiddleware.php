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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->metarisc->authenticate('oauth2:null', []);

        if ('/connection' != $request->getUri()->getPath() && '/' != $request->getUri()->getPath() && '/logout' != $request->getUri()->getPath() && !isset($request->getQueryParams()['code'])) {
            try {
                // REQUETE POUR VOIR SI LE USER EST CONNECTE
                $response = $this->metarisc->request('GET', '/@moi', ['auth' => 'oauth']);

                // VERIFICATION QUE LA RESPONSE NE SONT OK (!=200)
                if (200 != $response->getStatusCode() || !isset($_COOKIE['access_token'])) {
                    // throw new \Exception('Action non autorisé', 401);
                    header('Location: http://localhost:8000/');
                }
            } catch (\Exception $e) {
                header('Location: http://localhost:8000/');
            }
        }

        return $handler->handle($request);
    }
}
