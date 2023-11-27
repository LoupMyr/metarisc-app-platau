<?php

namespace App;

use App\Service\TokenPersistenceService;
use kamermans\OAuth2\Persistence\TokenPersistenceInterface;
use Laminas;
use GuzzleHttp;
use Assert\Assertion;
use Laminas\Session\SessionManager;
use Twig\Environment;
use Metarisc\Metarisc;
use League\Fractal\Manager;
use App\Service\SessionService;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use App\Service\UserCacheService;
use Twig\Loader\FilesystemLoader;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Container\ContainerInterface;
use App\Repository\UserCacheRepository;
use Laminas\Di\Container\ConfigFactory;
use Symfony\Component\Cache\Psr16Cache;
use Psr\Http\Message\UriFactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Spatie\Fractalistic\Fractal as Fractalistic;
use App\Domain\Service\UserCacheServiceInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use App\Domain\Repository\UserCacheRepositoryInterface;

class Container extends ServiceManager
{
    public static function initWithDefaults(array $options = []) : self
    {
        // Setup service manager
        $params = [
            'services' => [
                'config' => $options,
            ],
            'invokables' => [
                // PSR-17 HTTP Message Factories
                RequestFactoryInterface::class       => GuzzleHttp\Psr7\HttpFactory::class,
                ServerRequestFactoryInterface::class => GuzzleHttp\Psr7\HttpFactory::class,
                ResponseFactoryInterface::class      => GuzzleHttp\Psr7\HttpFactory::class,
                StreamFactoryInterface::class        => GuzzleHttp\Psr7\HttpFactory::class,
                UploadedFileFactoryInterface::class  => GuzzleHttp\Psr7\HttpFactory::class,
                UriFactoryInterface::class           => GuzzleHttp\Psr7\HttpFactory::class,

                // PSR-18 HTTP Client implementations
                ClientInterface::class => GuzzleHttp\Client::class,
            ],
            'factories' => [
                Laminas\Di\ConfigInterface::class   => ConfigFactory::class,
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
                $loader = new FilesystemLoader(__DIR__.'/../templates');

                return new Environment($loader);
            }
        );

        $container->setFactory(
            CacheInterface::class,
            function () {
                $psr6Cache = new FilesystemAdapter('metarisc-platau', 3600, __DIR__.'/../cache');

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

                $tokenPersistence = $container->get(TokenPersistenceInterface::class);
                \assert($tokenPersistence instanceof TokenPersistenceService);

                $metarisc->getClient()->setTokenPersistence($tokenPersistence);

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
            function (ContainerInterface $container) {
                $sessionsManager = $container->get(SessionManager::class);
                assert($sessionsManager instanceof SessionManager);
                return new SessionService($sessionsManager);
            }
        );

        $container->setFactory(
            UserCacheRepositoryInterface::class,
            function (ContainerInterface $container) {
                $em = $container->get(EntityManager::class);
                \assert($em instanceof EntityManager);

                return new UserCacheRepository($em);
            }
        );

        $container->setFactory(
            UserCacheServiceInterface::class,
            function (ContainerInterface $container) {
                $repository = $container->get(UserCacheRepositoryInterface::class);
                \assert($repository instanceof UserCacheRepository);

                return new UserCacheService($repository);
            }
        );

        $container->setFactory(
            TokenPersistenceInterface::class,
            function (ContainerInterface $container) {
                $sessionService = $container->get(SessionService::class);
                \assert($sessionService instanceof SessionService);
                $sessionManager = $container->get(SessionManager::class);
                \assert($sessionManager instanceof SessionManager);
                return new TokenPersistenceService($sessionService, $sessionManager);
            }
        );

        $container->setFactory(
            SessionManager::class,
            function(){
                return new SessionManager();
            }
        );

        return $container;
    }
}
