<?php

namespace App\Http\Controller;

use Assert\Assertion;
use Twig\Environment;
use Metarisc\Metarisc;
use Metarisc\Model\Notification;
use Laminas\Diactoros\ResponseFactory;
use Metarisc\Service\NotificationsAPI;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotificationController
{
    public function __construct(
        private Metarisc $metarisc,
        private Environment $twig,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args = []) : ResponseInterface
    {
        $template             = $this->twig->load('notifications.twig');
        $notificationsService = $this->metarisc->notifications;
        \assert($notificationsService instanceof NotificationsAPI);

        $notificationsPager   = $notificationsService->paginateNotifications();
        $notifications        = $notificationsPager->getCurrentPageResults();
        // $notifications      = [];
        // foreach ($notifsArray as $elt) {
        //     Assertion::isArray($elt);
        //     $notif           = Notification::unserialize($elt);
        //     $notifications[] = $notif;
        // }
        $html               = $template->render([
            'notifications' => $notifications,
        ]);

        $responseFactory = new ResponseFactory();
        $response        = $responseFactory->createResponse();

        $response->getBody()->write($html);

        return $response;
    }
}
