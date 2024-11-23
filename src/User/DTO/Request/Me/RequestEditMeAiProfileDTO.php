<?php

namespace App\User\DTO\Request\Me;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class RequestEditMeAiProfileDTO
{
    #[Assert\NotBlank]
    #[ApiProperty(example: '{"name": "Sasha Gray"}')]
    public string $profile;
}