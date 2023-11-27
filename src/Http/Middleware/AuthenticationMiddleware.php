<?php

namespace App\Http\Middleware;

use Laminas\Session\SessionManager;
use Metarisc\Metarisc;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Metarisc       $metarisc,
        private SessionManager $sessionManager
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->metarisc->authenticate('oauth2:null', []);

        if ($this->sessionManager->sessionExists() && $this->sessionManager->getStorage()->getMetadata('access_token')) {

            $rep = $this->metarisc->request('GET', '/@moi', ['auth' => 'oauth']);
            //var_dump('ca passe l authentification');
            if ($rep->getStatusCode() != 200) {
                throw new \Exception('error dans le middleware, pas possible de faire la requete', 401);
            }
        } else {
            throw new \Exception('error dans le middleware, au niveau de la session', 401);
            /*header('Location: http://localhost:8000/');
            exit;*/
        }

        return $handler->handle($request);
    }
}
