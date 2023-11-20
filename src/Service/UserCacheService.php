<?php

namespace App\Service;

use App\Domain\Entity\UserCache;
use App\Domain\Repository\UserCacheRepositoryInterface;
use App\Domain\Service\UserCacheServiceInterface;

class UserCacheService implements UserCacheServiceInterface
{
    public function __construct(
        private UserCacheRepositoryInterface $repository
    ){
    }

    public function addUserCache(UserCache $userCache): void
    {
        $this->repository->insert($userCache);
    }

    public function deleteUserCache(string $email): void
    {
        $this->repository->deleteByEmail($email);
    }

    public function updateUserCache(string $userCache_email, UserCache $userCache): void
    {
        $this->repository->update($userCache_email, $userCache);
    }

    public function getUserCacheByEmail(string $email): ?UserCache
    {
        return $this->repository->getByEmail($email);
    }
}
