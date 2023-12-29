<?php

namespace App\Domain\Service;

use Psr\SimpleCache\CacheInterface;

interface SessionServiceInterface
{

    public function destroySession(CacheInterface $cache) : void;

    public function isConnected() : bool; 

    public function setAllCookies() : void;

    public function setSessionCookies(array $cookies) : void;

    public function hasSessionCookiesToken() : bool;

    public function getSessionCookiesToken() : mixed;
}
