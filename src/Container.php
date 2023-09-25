<?php

namespace App;

use App\Http\Middleware\AuthenticationMiddleware;
use Laminas;
use GuzzleHttp;
use Symfony\Component\Cache\Psr16Cache;
use Twig\Environment;
use Metarisc\Metarisc;
use League\Fractal\Manager;
use Twig\Loader\FilesystemLoader;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Container\ContainerInterface;
use Laminas\Di\Container\ConfigFactory;
use Psr\Http\Message\UriFactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Spatie\Fractalistic\Fractal as Fractalistic;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Container extends ServiceManager
{
    public static function initWithDefaults(array $options = []) : self
    {
        // Setup service manager
        $params = [
            'services'   => [
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
                ClientInterface::class               => GuzzleHttp\Client::class,
            ],
            'factories'  => [
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
                $loader =  new FilesystemLoader(__DIR__.'/../templates');

                return new Environment($loader);
            }
        );

        $container->setFactory(
            CacheInterface::class,
            function () {

                $psr6Cache = new FilesystemAdapter('metarisc-platau', 3600 , __DIR__.'/../cache');
                return new Psr16Cache($psr6Cache);
            }
        );

        $container->setFactory(
            Metarisc::class,
            function (ContainerInterface $container) {

                $config = $container->get('config');
                assert(is_array($config));

                $metarisc_params = $config[Metarisc::class];
                return new Metarisc($metarisc_params);
            }
        );

        return $container;
    }
}
