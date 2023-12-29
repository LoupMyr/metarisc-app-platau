<?php

namespace App\Service;

use App\Domain\Entity\UserCache;
use App\Domain\Service\UserCacheServiceInterface;
use App\Domain\Repository\UserCacheRepositoryInterface;

class UserCacheService implements UserCacheServiceInterface
{
    public function __construct(
        private UserCacheRepositoryInterface $repository
    ) {
    }

    public function addUserCache(UserCache $userCache) : void
    {
        $this->repository->insert($userCache);
    }

    public function deleteUserCache(string $email) : void
    {
        $this->repository->deleteByEmail($email);
    }

    public function updateUserCache(string $userCache_email, UserCache $userCache) : void
    {
        $this->repository->update($userCache_email, $userCache);
    }

    public function getUserCacheByEmail(string $email) : ?UserCache
    {
        return $this->repository->getByEmail($email);
    }

    public function getAllUserCache() : array
    {
        return $this->repository->getAll();
    }
}
