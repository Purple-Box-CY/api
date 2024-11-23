<?php

namespace App\User\DTO\Request;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class RequestResetPasswordDTO
{
    #[Assert\Email]
    #[Assert\Length(
        min: 6,
        max: 32
    )]
    #[ApiProperty(example: 'user@gmail.com')]
    public string $email;
}