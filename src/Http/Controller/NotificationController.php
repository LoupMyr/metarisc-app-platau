<?php

namespace App\Http\Controller;

use App\Http\Middleware\AuthenticationMiddleware;
use Laminas\Diactoros\ResponseFactory;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Route\Router;
use Metarisc\Metarisc;
use Pagerfanta\Pagerfanta;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Twig\Environment;
use Spatie\Fractalistic\Fractal as Fractalistic;

class NotificationController
{
    public function __construct(
        private Metarisc $metarisc,
        private Environment $twig,
    )
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args = []) : ResponseInterface
    {
        $template = $this->twig->load('notifications.twig');


        $notificationsPager = $this->metarisc->notifications->paginateNotifications();
        $notifications = $notificationsPager->getCurrentPageResults();


        $html = $template->render([
            'notifications' => $notifications,
        ]);

        $responseFactory = new ResponseFactory();
        $response        = $responseFactory->createResponse();

        $response->getBody()->write($html);



        return $response;

    }
}