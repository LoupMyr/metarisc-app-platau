<?php

namespace App\Service;

use App\Domain\Service\SessionServiceInterface;
use Assert\Assertion;
use Laminas\Session\SessionManager;
use Psr\SimpleCache\CacheInterface;
use Laminas\Session\Storage\SessionStorage;

class SessionService implements SessionServiceInterface
{
    public function __construct(
        private SessionManager $sessionManager
    ) {
    }

    public function destroySession(CacheInterface $cache) : void
    {
        if ($this->sessionManager->sessionExists()) {
            $this->sessionManager->writeClose();
            $cache->clear();
        }
    }

    public function isConnected() : bool
    {
        $isConnected = true;
        if (2 != session_status()) {
            $isConnected = false;
        }

        return $isConnected;
    }

    public function setAllCookies() : void
    {
        $email  =$this->sessionManager->getStorage()->getMetadata('email');
        $access = $this->sessionManager->getStorage()->getMetadata('access_token');
        Assertion::string($email);
        Assertion::string($access);
        setcookie('email', $email, 0, '/', secure: true, httponly: true);
        setcookie('access_token', $access, 0, '/', secure: true, httponly: true);
    }

    
    public function setSessionCookies(array $cookies) : void
    {
        $this->setSessionStorage();
        /** @var string|int $value */
        foreach ($cookies as $key => $value) {
            Assertion::string($key);
            $this->sessionManager->getStorage()->setMetadata($key, $value);
        }
    }
    
    public function hasSessionCookiesToken() : bool
    {
        return (bool) $this->sessionManager->getStorage()->getMetadata('access_token');
    }
    
    public function getSessionCookiesToken() : mixed
    {
        if ($this->hasSessionCookiesToken()) {
            return $this->sessionManager->getStorage()->getMetadata('access_token');
        }
        
        return null;
    }
    
    private function setSessionStorage() : void
    {
        if (null == $this->sessionManager->getStorage()) {
            $sessionStorage = new SessionStorage();
            $this->sessionManager->setStorage($sessionStorage);
        }
    }
}
