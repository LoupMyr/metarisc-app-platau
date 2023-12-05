<?php

namespace App\Http\Middleware;

use Assert\Assertion;
use Metarisc\Metarisc;
use Laminas\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Domain\Service\UserCacheServiceInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Metarisc $metarisc,
        private SessionManager $sessionManager,
        private UserCacheServiceInterface $userCacheService
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->metarisc->authenticate('oauth2:null', [
            'enable_refresh_token_grant_type' => true,
        ]);

        if ($this->sessionManager->sessionExists() && $this->sessionManager->getStorage()->getMetadata('access_token')) {
            $rep = $this->metarisc->request('GET', '/@moi', ['auth' => 'oauth']);

            if (200 != $rep->getStatusCode()) {
                throw new \Exception('error dans le middleware, pas possible de faire la requete', 401);
            }

            // On récupere les informations necessaire a la verification de l'access_token dans la base de donnée
            $email = $this->sessionManager->getStorage()->getMetadata('email');
            Assertion::string($email);
            $userCache = $this->userCacheService->getUserCacheByEmail($email);
            Assertion::notNull($userCache);
            $accessDB      = $userCache->getAccessToken();
            $refreshDB     = $userCache->getRefreshToken();
            $access_token  = $this->sessionManager->getStorage()->getMetadata('access_token');
            $refresh_token = $this->sessionManager->getStorage()->getMetadata('refresh_token');
            Assertion::string($access_token);
            Assertion::string($refresh_token);

            // On verifie que notre $access_token est le même que celui en base de donée, sinon on le remplace en base de donnée. Ainsi que le refresh_token
            if ($access_token != $accessDB || $refresh_token != $refreshDB) {
                if ($access_token != $accessDB) {
                    $userCache->setAccessToken($access_token);
                }
                if ($refresh_token != $refreshDB) {
                    $userCache->setRefreshToken($refresh_token);
                }
                $this->userCacheService->updateUserCache($email, $userCache);
            }
        } else {
            header('Location: http://localhost:8000/');
            exit;
        }

        return $handler->handle($request);
    }
}
