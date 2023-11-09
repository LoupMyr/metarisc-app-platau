<?php

namespace App\Service;

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

    public function destroySession() : void
    {
        session_destroy();
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
