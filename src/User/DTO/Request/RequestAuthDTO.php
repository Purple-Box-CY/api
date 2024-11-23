<?php

namespace App\User\DTO\Request;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class RequestAuthDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 6,
        max: 128
    )]
    #[ApiProperty(example: 'user@gmail.com')]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(
        min: 6,
        max: 128
    )]
    #[ApiProperty(example: '01hda4ewq')]
    public string $password;

    #[Assert\Length(
        max: 32
    )]
    #[ApiProperty(example: 'cms')]
    public ?string $authType = null;

}