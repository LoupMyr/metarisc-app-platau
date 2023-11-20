<?php

namespace App;

use App\Domain\Repository\UserCacheRepositoryInterface;
use App\Domain\Service\UserCacheServiceInterface;
use App\Service\UserCacheService;
use Laminas;
use GuzzleHttp;
use Assert\Assertion;
use Twig\Environment;
use Metarisc\Metarisc;
use League\Fractal\Manager;
use App\Service\SessionService;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Twig\Loader\FilesystemLoader;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Container\ContainerInterface;
use Laminas\Di\Container\ConfigFactory;
use Symfony\Component\Cache\Psr16Cache;
use Psr\Http\Message\UriFactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Spatie\Fractalistic\Fractal as Fractalistic;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use App\Repository\UserCacheRepository;

class Container extends ServiceManager
{
    public static function initWithDefaults(array $options = []): self
    {
        // Setup service manager
        $params = [
            'services' => [
                'config' => $options,
            ],
            'invokables' => [
                // PSR-17 HTTP Message Factories
                RequestFactoryInterface::class => GuzzleHttp\Psr7\HttpFactory::class,
                ServerRequestFactoryInterface::class => GuzzleHttp\Psr7\HttpFactory::class,
                ResponseFactoryInterface::class => GuzzleHttp\Psr7\HttpFactory::class,
                StreamFactoryInterface::class => GuzzleHttp\Psr7\HttpFactory::class,
                UploadedFileFactoryInterface::class => GuzzleHttp\Psr7\HttpFactory::class,
                UriFactoryInterface::class => GuzzleHttp\Psr7\HttpFactory::class,

                // PSR-18 HTTP Client implementations
                ClientInterface::class => GuzzleHttp\Client::class,
            ],
            'factories' => [
                Laminas\Di\ConfigInterface::class => ConfigFactory::class,
                Laminas\Di\InjectorInterface::class => Laminas\Di\Container\InjectorFactory::class,
            ],
        ];

        $container = new self($params);

        $container->setFactory(
            Fractalistic::class,
            function () {
                return new Fractalistic(
                    new Manager()
                );
            }
        );

        $container->setFactory(
            Environment::class,
            function () {
                $loader = new FilesystemLoader(__DIR__ . '/../templates');

                return new Environment($loader);
            }
        );

        $container->setFactory(
            CacheInterface::class,
            function () {
                $psr6Cache = new FilesystemAdapter('metarisc-platau', 3600, __DIR__ . '/../cache');
                return new Psr16Cache($psr6Cache);
            }
        );

        $container->setFactory(
            Metarisc::class,
            function (ContainerInterface $container) {
                $config = $container->get('config');
                \assert(\is_array($config));

                $metarisc_params = $config[Metarisc::class];
                Assertion::isArray($metarisc_params);
                $metarisc = new Metarisc($metarisc_params);

                $cache = $container->get(CacheInterface::class);
                \assert($cache instanceof CacheInterface);

                $metarisc->getClient()->setTokenPersistence($cache);

                return $metarisc;
            }
        );

        $container->setFactory(
            EntityManager::class,
            function (ContainerInterface $container) {
                $config = $container->get('config');
                Assertion::isArray($config);
                /** @var array{charset?:string} $em_conn */
                $em_conn = $config['em_conn'];

                $em_config = $config['em_config'];
                Assertion::isInstanceOf($em_config, Configuration::class);
                $conn = DriverManager::getConnection($em_conn, $em_config);

                return new EntityManager(
                    $conn,
                    $em_config
                );
            }
        );

        $container->setFactory(
            SessionService::class,
            function () {
                return new SessionService();
            }
        );

        $container->setFactory(
            UserCacheRepositoryInterface::class,
            function (ContainerInterface $container) {
                $em = $container->get(EntityManager::class);
                assert($em instanceof EntityManager);
                return new UserCacheRepository($em);
            }
        );

        $container->setFactory(
            UserCacheServiceInterface::class,
            function(ContainerInterface $container){
                $repository = $container->get(UserCacheRepositoryInterface::class);
                assert($repository instanceof UserCacheRepository);
                return new UserCacheService($repository);
            }
        );

        return $container;
    }
}
