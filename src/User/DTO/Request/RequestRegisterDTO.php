<?php

namespace App\User\DTO\Request;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Settings\Country;
use Symfony\Component\Validator\Constraints as Assert;

class RequestRegisterDTO
{
    #[Assert\Email]
    #[Assert\Length(
        min: 6,
        max: 128
    )]
    #[ApiProperty(example: 'user@gmail.com')]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(
        min: 6,
        max: 32
    )]
    #[ApiProperty(example: '01hda4ewq')]
    public string $password;

    #[Assert\Length(
        min: 2,
        max: 64
    )]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9\-\_]+$/")]
    #[ApiProperty(example: 'john_connor')]
    public ?string $name = null;

    #[ApiProperty(example: '01HEJAQQJ8S1YJ7NFD4GBY7HTV')]
    public ?string $contentId = null;

    #[ApiProperty(example: '01HEJ5JZ52Z449R6HM5J985S9N')]
    public ?string $userUid = null;

    #[Assert\Length(
        min: 2,
        max: 2
    )]
    #[Assert\Choice(choices: Country::CODES)]
    #[ApiProperty(example: 'CY')]
    public ?string $country = null;

    #[ApiProperty(example: 'chat')]
    public ?string $source = null;
}
