<?php

namespace App\Http;

use Laminas\Di;
use League\Route\Router;
use Psr\Container\ContainerInterface;
use App\Http\Controller\HomeController;
use Psr\Http\Message\ResponseInterface;
use App\Http\Controller\AccessController;
use App\Http\Middleware\CachingMiddleware;
use App\Http\Controller\EvenementController;
use Laminas\Di\Exception\ExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Http\Controller\ConnectionController;
use App\Http\Controller\NotificationController;
use App\Http\Controller\OrganisationController;
use App\Http\Middleware\AuthenticationMiddleware;

final class HttpPipeline implements RequestHandlerInterface
{
    /**
     * Initialisation du pipeline HTTP avec un Container servant à l'injection des dépendances.
     */
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    /**
     * La requête passe dans un pipeline HTTP afin de produire une réponse.
     *
     * @throws ExceptionInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $router = new Router();

        $injector = $this->container->get(Di\InjectorInterface::class);

        if (!($injector instanceof Di\InjectorInterface)) {
            throw new \Exception("Injector must be an instance of Di\InjectorInterface");
        }
        $router->middleware($injector->create(AuthenticationMiddleware::class));
        $router->middleware($injector->create(CachingMiddleware::class));

        $router->get('/', $injector->create(HomeController::class));
        $router->get('/access', $injector->create(AccessController::class));
        $router->post('/access', $injector->create(AccessController::class));
        $router->get('/connection', $injector->create(ConnectionController::class));
        $router->post('/connection', $injector->create(ConnectionController::class));
        $router->get('/notifications', $injector->create(NotificationController::class));
        $router->get('/organisation', $injector->create(OrganisationController::class));
        $router->get('/evenements', $injector->create(EvenementController::class));
        $router->post('/evenements', $injector->create(EvenementController::class));

        return $router->dispatch($request);
    }
}
