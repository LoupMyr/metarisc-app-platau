<?php

namespace App\Http\Controller;

use Laminas;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Twig\Environment;
use Metarisc\Metarisc;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessController
{
    public function __construct(
        private Metarisc $metarisc,
        private Environment $twig,
        private CacheInterface $cache
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args = []) : ResponseInterface
    {
        $template = $this->twig->load('home.twig');

        $code = $_GET['code'];

        $this->metarisc->authenticate('oauth2:client_credentials', [
            'code' => $code,
            'scope'        => 'openid profile email',
        ],$this->cache );


        //celui ci ne fonction pas
        //$profil = $this->metarisc->utilisateurs->getUtilisateursMoi1();

        $profil      = $this->metarisc->request('GET', '/@moi', ['auth' => 'oauth'])->getBody()->__toString();
       // $notificationsPager = $this->metarisc->request('GET', '/notifications/13b5c620-3d36-440f-bbbd-f9e3f594af87', ['auth' => 'oauth'])->getBody()->__toString();

        $profil_json = json_decode($profil, true);
        //$notifications = json_decode($notificationsPager, true);

        $html = $template->render([
            'user_profile' => $profil_json,
           // 'notifications'=> $notifications
        ]);

        var_dump($profil_json);
        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();

        $response->getBody()->write($html);

        return $response;
    }
}
