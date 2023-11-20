<?php

namespace App\Domain\Repository;

use App\Domain\Entity\UserCache;

interface UserCacheRepositoryInterface
{
    /**
     * Ajout d'un nouveau userCache.
     */
    public function insert(UserCache $userCache) : void;

    /**
     * Mets à jour un userCache.
     */
    public function update(string $userCache_email, UserCache $userCache) : void;

    /**
     * Récupère un userCache avec un email donné.
     */
    public function getByEmail(string $email) : UserCache|null;

    /**
     * Supprime un userCache grâce à son email donné.
     */
    public function deleteByEmail(string $email) : void;
}
