<?php

namespace App\Command;

use App\Container;
use Dotenv\Dotenv;
use Assert\Assertion;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\UserCache;
use App\Domain\Service\UserCacheServiceInterface;

require __DIR__.'/../../vendor/autoload.php';

// Récupération de la config nécessaire pour créer un Container.
$dotenv = Dotenv::createUnsafeImmutable(__DIR__.'/../');
$dotenv->safeLoad();
$config = require __DIR__.'/../../config/config.php';

// Initialisation d'un nouveau Container.
$container = Container::initWithDefaults($config);

// Création d'un UserCacheServiceInterface grâce au Container.
$userCacheService = $container->get(UserCacheServiceInterface::class);
Assertion::isInstanceOf($userCacheService, UserCacheServiceInterface::class);

// Création d'un Logger grâce au Container.
$logger = $container->get(LoggerInterface::class);
Assertion::isInstanceOf($logger, LoggerInterface::class);

// Récupération de tous les userCache. Si il n'y en pas (array vide), on arrête tout et génére une log de warning.
$users = $userCacheService->getAllUserCache();
if (empty($users)) {
    $logger->warning('No user found.');
    exit;
}

// Tout s'est bien passé, on log tous les utilisateurs.
foreach ($users as $key => $user) {
    Assertion::isInstanceOf($user, UserCache::class);
    $logger->log(0, $key.". Identifiant Plat'au: ".$user->getIdPlatau());
}
