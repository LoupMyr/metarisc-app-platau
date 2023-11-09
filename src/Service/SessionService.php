<?php

namespace App\Service;

use Psr\SimpleCache\CacheInterface;

class SessionService
{
    public function __construct()
    {
    }

    public function createSecureParams() : void
    {
        session_set_cookie_params([
            'secure'   => true,
            'httponly' => true,
        ]);
    }

    public function startSecureSession() : void
    {
        $this->createSecureParams();
        session_start();
    }

    public function destroySession(CacheInterface $cache) : void
    {
        session_destroy();
        setcookie('access_token');
        $cache->clear();
    }

    public function isConnected() : bool
    {
        $isConnected = true;
        if (2 != session_status()) {
            $isConnected = false;
        }

        return $isConnected;
    }

    public function setAllCookies(string $access, string $email) : void
    {
        setcookie('email', $email, 0, '/', secure: true, httponly: true);
        setcookie('access_token', $access, 0, '/', secure: true, httponly: true);
    }
}
