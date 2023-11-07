<?php

use App\Container;
use Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;

require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv::createUnsafeImmutable(__DIR__.'/../');
$dotenv->safeLoad();

$config = require __DIR__.'/config/config.php';

$container = Container::initWithDefaults($config);

$config = new PhpFile(__DIR__.'/config/migrations.php');

$em = $container->get(EntityManager::class);

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($em));
