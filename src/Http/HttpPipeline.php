<?php

namespace App\Http;

use Laminas\Di;
use League\Route\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Http\Controller\AccessController;
use App\Http\Controller\LogoutController;
use App\Http\Controller\FormMenuController;
use App\Http\Controller\ErrorAuthController;
use App\Http\Controller\EvenementController;
use Laminas\Di\Exception\ExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Http\Controller\ConnectionController;
use App\Http\Controller\NotificationController;
use App\Http\Controller\OrganisationController;
use App\Http\Middleware\AuthenticationMiddleware;
use App\Http\Middleware\SessionManagerMiddleware;

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

        // Connection route
        $router
            ->get('/', $injector->create(ConnectionController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class));
        $router
            ->get('/access', $injector->create(AccessController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class));

        $router
            ->get('/error', $injector->create(ErrorAuthController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class));
        $router
            ->get('/home', $injector->create(FormMenuController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class))
            ->middleware($injector->create(AuthenticationMiddleware::class));

        $router
            ->post('/home', $injector->create(FormMenuController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class))
            ->middleware($injector->create(AuthenticationMiddleware::class));

        $router
            ->get('/logout', $injector->create(LogoutController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class));

        $router
            ->get('/notifications', $injector->create(NotificationController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class))
            ->middleware($injector->create(AuthenticationMiddleware::class));
        $router
            ->get('/organisation', $injector->create(OrganisationController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class))
            ->middleware($injector->create(AuthenticationMiddleware::class));

        $router
            ->get('/evenements', $injector->create(EvenementController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class))
            ->middleware($injector->create(AuthenticationMiddleware::class));

        $router
            ->post('/evenements', $injector->create(EvenementController::class))
            ->middleware($injector->create(SessionManagerMiddleware::class))
            ->middleware($injector->create(AuthenticationMiddleware::class));

        return $router->dispatch($request);
    }
}
