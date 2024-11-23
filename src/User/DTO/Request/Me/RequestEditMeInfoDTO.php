<?php

namespace App\User\DTO\Request\Me;

use ApiPlatform\Metadata\ApiProperty;
use Monolog\DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class RequestEditMeInfoDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 128
    )]
    #[ApiProperty(example: 'John Connor')]
    public string $fullName;

    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 128
    )]
    #[Assert\Regex(pattern: "/^[a-zA-Z0-9\-\_]+$/")]
    #[ApiProperty(example: 'john_connor')]
    public string $username;

    #[Assert\Length(max: 1024)]
    #[ApiProperty(example: 'World Champion')]
    public string $description = '';

    #[Assert\Date(message: 'This value is not a valid date')]
    #[ApiProperty(example: '1989-09-23')]
    public ?string $birthDate = null;

    #[Assert\Choice(choices: ['male', 'female', 'other'])]
    #[ApiProperty(description: 'choices: male, female, other', example: 'male')]
    public ?string $gender = null;

    #[Assert\Length(
        min: 2,
        max: 2
    )]
    #[ApiProperty(example: 'CY')]
    public ?string $country = null;

    #[Assert\Length(
        min: 2,
        max: 2
    )]
    #[ApiProperty(example: 'EN')]
    public ?string $language = null;

    #[Assert\Length(
        min: 2,
        max: 128
    )]
    #[ApiProperty(example: 'Limassol')]
    public ?string $city = null;

    #[Assert\Length(
        max: 1024
    )]
    #[ApiProperty(example: 'I\'m a Barbie girl in the Barbie world\nLife in plastic, it\'s fantastic')]
    public ?string $slogan = '';
}
