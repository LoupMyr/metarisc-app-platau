<?php

namespace App\Domain\Entity;

use Ramsey\Uuid\Uuid;

class UserCache
{
    private string $id;
    private string $email;
    private bool $option1;
    private string $access_token;
    private string $refresh_token;
    private string $idPlatau;

    public function __construct(
        string $email,
        bool $option1,
        string $access_token,
        string $refresh_token,
        string $idPlatau,
    ) {
        $this->id            = Uuid::uuid4()->toString();
        $this->email         = $email;
        $this->option1       = $option1;
        $this->access_token  = $access_token;
        $this->refresh_token = $refresh_token;
        $this->idPlatau      = $idPlatau;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    public function getOption1() : bool
    {
        return $this->option1;
    }

    public function setOption1(bool $option1) : void
    {
        $this->option1 = $option1;
    }

    public function getAccessToken() : string
    {
        return $this->access_token;
    }

    public function setAccessToken(string $access_token) : void
    {
        $this->access_token = $access_token;
    }

    public function getRefreshToken() : string
    {
        return $this->refresh_token;
    }

    public function setRefreshToken(string $refresh_token) : void
    {
        $this->refresh_token = $refresh_token;
    }

    public function getIdPlatau() : string
    {
        return $this->idPlatau;
    }

    public function setIdPlatau(string $idPlatau) : void
    {
        $this->idPlatau = $idPlatau;
    }
}
