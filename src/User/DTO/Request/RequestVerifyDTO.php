<?php

namespace App\User\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class RequestVerifyDTO
{
    #[Assert\NotBlank]
    public string $expires;

    #[Assert\NotBlank]
    public string $token;

    #[Assert\NotBlank]
    public string $signature;

    #[Assert\NotBlank]
    public string $uid;
}

