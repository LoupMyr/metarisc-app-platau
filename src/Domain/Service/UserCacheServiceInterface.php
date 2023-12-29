<?php

namespace App\Domain\Service;

use App\Domain\Entity\UserCache;

interface UserCacheServiceInterface
{
    public function addUserCache(UserCache $userCache) : void;

    public function deleteUserCache(string $email) : void;

    public function updateUserCache(string $userCache_email, UserCache $userCache) : void;

    public function getUserCacheByEmail(string $email) : ?UserCache;

    public function getAllUserCache() : array;
}
