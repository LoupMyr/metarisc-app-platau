<?php

namespace App\Service;

use App\Domain\Entity\UserCache;
use Assert\Assertion;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface;
use kamermans\OAuth2\Token\TokenInterface;

class TokenPersistenceService implements TokenPersistenceInterface
{


    public function __construct(
    )
    {
    }

    public function restoreToken(TokenInterface $token)
    {
        // TODO: Implement restoreToken() method.
    }

    public function saveToken(TokenInterface $token)
    {
        // TODO: Implement saveToken() method.
    }

    public function deleteToken()
    {
        // TODO: Implement deleteToken() method.
    }

    public function hasToken()
    {
        // TODO: Implement hasToken() method.
    }
}