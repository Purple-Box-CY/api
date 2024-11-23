<?php

namespace App\User\DTO\Request;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class RequestNewPasswordDTO
{
    #[Assert\Length(
        min: 6,
        max: 32
    )]
    #[ApiProperty(example: 'j3aJasg*dj2d;!kf')]
    public string $password;
    public string $sign;
}
