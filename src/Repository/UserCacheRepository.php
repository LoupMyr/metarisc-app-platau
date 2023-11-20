<?php

namespace App\Repository;

use App\Domain\Entity\UserCache;
use App\Domain\Repository\UserCacheRepositoryInterface;
use Doctrine\ORM\EntityManager;

class UserCacheRepository implements UserCacheRepositoryInterface
{

    public function __construct(
        private EntityManager $entityManager
    ){
    }

    public function insert(UserCache $userCache): void
    {
        $this->entityManager->persist(
            $userCache
        );
        $this->entityManager->flush();
    }

    public function update(string $userCache_email, UserCache $userCache): void
    {
        $oldUtilisateur = $this->getByEmail($userCache_email);
        \assert($oldUtilisateur instanceof UserCache);
        \assert($this->entityManager->contains($oldUtilisateur),
            "L'utilisateur n'est pas tracked par doctrine"
        );

        $oldUtilisateur->setEmail($userCache->getEmail());
        $oldUtilisateur->setOption1($userCache->getOption1());
        $oldUtilisateur->setAccessToken($userCache->getAccessToken());
        $oldUtilisateur->setRefreshToken($userCache->getRefreshToken());

        $this->entityManager->persist($oldUtilisateur);
        $this->entityManager->flush();
    }

    public function getByEmail(string $email): UserCache|null
    {
        return $this->entityManager->find(
            UserCache::class,
            $email
        );
    }

    public function deleteByEmail(string $email): void
    {
        $userCache = $this->getByEmail($email);
        if(!($userCache instanceof UserCache)){
            throw new \Exception('UserCache pas dispo');
        }

        $this->entityManager->remove(
            $userCache
        );
        $this->entityManager->flush();
    }
}