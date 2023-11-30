<?php

namespace App\Service;

use Assert\Assertion;
use Laminas\Session\SessionManager;
use kamermans\OAuth2\Token\Serializable;
use kamermans\OAuth2\Token\TokenInterface;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface;

class TokenPersistenceService implements TokenPersistenceInterface
{
    public function __construct(
        private SessionService $sessionService,
        private SessionManager $sessionManager
    ) {
    }

    /** @psalm-suppress InvalidNullableReturnType */
    public function restoreToken(TokenInterface $token) : TokenInterface|null
    {
        /** @var string|bool $access */
        $access  = $this->sessionManager->getStorage()->getMetadata('access_token');
        /** @var int|bool $expires */
        $expires = $this->sessionManager->getStorage()->getMetadata('expires_at');
        /** @var string|bool $refresh */
        $refresh = $this->sessionManager->getStorage()->getMetadata('refresh_token');
        if (!$access) {
            /** @psalm-suppress NullableReturnStatement */
            return null;
        }

        if (!($token instanceof Serializable)) {
            throw new \Exception("Le token ne peut pas etre unserialize. Il n'implémente pas Serializable.");
        }

        $unserialize = $token->unserialize([
            'access_token'  => $access,
            'expires_at'    => $expires,
            'refresh_token' => $refresh,
        ]);
        Assertion::isInstanceOf($unserialize, TokenInterface::class);

        return $unserialize;
    }

    public function saveToken(TokenInterface $token) : void
    {
        if (!($token instanceof Serializable)) {
            throw new \Exception("Le token ne peut pas etre serialize. Il n'implémente pas Serializable.");
        }

        if (!$token->isExpired()) {
            /** @var array<string,string> $tokenSerialize */
            $tokenSerialize = $token->serialize();
            $this->sessionService->setSessionCookies($tokenSerialize);
        } else {
            throw new \Exception('Token expired');
        }
    }

    public function deleteToken() : void
    {
        $this->sessionManager->getStorage()->clear('access_token');
    }

    public function hasToken() : bool
    {
        return (bool) $this->sessionManager->getStorage()->getMetadata('access_token');
    }
}
