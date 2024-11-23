<?php

namespace App\User\DTO\Request;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class RequestUpdatePasswordDTO
{
    #[Assert\Length(
        min: 6,
        max: 32
    )]
    #[ApiProperty(example: 'j3aJasg*dj2d;!kf')]
    public string $oldPassword;
    #[Assert\Length(
        min: 6,
        max: 32
    )]
    #[ApiProperty(example: 'gj13kh0(d902e#),')]
    public string $newPassword;
}
