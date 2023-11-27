<?php

namespace App\Service;

use kamermans\OAuth2\Persistence\TokenPersistenceInterface;
use kamermans\OAuth2\Token\TokenInterface;
use Laminas\Session\SessionManager;

class TokenPersistenceService implements TokenPersistenceInterface
{


    public function __construct(
        private SessionService $sessionService,
        private SessionManager $sessionManager
    )
    {
    }

    public function restoreToken(TokenInterface $token): mixed
    {

        $access = $this->sessionManager->getStorage()->getMetadata('access_token');
        $expires = $this->sessionManager->getStorage()->getMetadata('expires_at');
        $refresh = $this->sessionManager->getStorage()->getMetadata('refresh_token');
        if(!$access){
            return null;
        }

        $unserialize = $token->unserialize([
            'access_token' => $access,
            'expires_at' => $expires,
            'refresh_token' => $refresh
        ]);

        return $unserialize;


    }

    public function saveToken(TokenInterface $token): void
    {
        if(!$token->isExpired()) {
            $this->sessionService->setSessionCookies($token->serialize());
        }else {
            throw new \Exception('Token expired');
        }
    }

    public function deleteToken(): void
    {
       $this->sessionManager->getStorage()->clear('access_token');
    }

    public function hasToken(): bool
    {
        return (bool)$this->sessionManager->getStorage()->getMetadata('access_token');
    }
}