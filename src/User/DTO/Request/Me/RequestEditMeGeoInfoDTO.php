<?php

namespace App\User\DTO\Request\Me;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Settings\Country;
use Symfony\Component\Validator\Constraints as Assert;

class RequestEditMeGeoInfoDTO
{
    #[Assert\Length(
        min: 2,
        max: 2
    )]
    #[Assert\Choice(choices: Country::CODES)]
    #[ApiProperty(example: 'CY')]
    #[Assert\NotBlank]
    public string $country;

    #[Assert\Length(
        min: 2,
        max: 128
    )]
    #[ApiProperty(example: 'Limassol')]
    public ?string $city = null;

}