<?php

namespace App\ApiDTO\Request\Marker;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Marker;
use Symfony\Component\Validator\Constraints as Assert;

class RequestCreateMarker
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    #[ApiProperty(example: '34.677720195690135')]
    public string $latitude;

    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    #[ApiProperty(example: '33.048458184988064')]
    public string $longitude;

    #[Assert\NotBlank]
    #[Assert\Choice(Marker::AVAILABLE_TYPES)]
    #[ApiProperty(example: 'paper')]
    public string $type;

    #[Assert\Length(max: 2200)]
    #[ApiProperty(example: 'Clothes box near school')]
    public ?string $description = null;
}
