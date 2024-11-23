<?php

namespace App\User\DTO\Request;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Settings\Country;
use Symfony\Component\Validator\Constraints as Assert;

class RequestRegisterAnonymDTO
{
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

    #[ApiProperty(example: '01J29DQ9H683FETZN9M185TN97')]
    public ?string $personalUid = null;

    public function getContentId(): ?string
    {
        return $this->contentId;
    }

    public function getUserUid(): ?string
    {
        return $this->userUid;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getPersonalUid(): ?string
    {
        return $this->personalUid;
    }

}