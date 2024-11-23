<?php

namespace App\User\DTO\Response\Me;

use ApiPlatform\Metadata\ApiProperty;
use App\User\Entity\Interfaces\AppUserInterface;
use App\User\Entity\User;

class ResponseMeMainInfoDTO
{
    public function __construct(
        public string              $uid = '',
        public string              $email = '',
        public bool                $isVerified = false,
        public bool                $isApproved = false,
        public ?bool               $isAI = false,
        public string              $status = User::STATUS_ACTIVE,
        public ?string             $username = '',
        public ?string             $avatar = '',
        public ?string             $imageProfile = '',
        public ?string             $photoProfile = '',
        public bool                $hasImageProfile = false,
        public ?string             $audioProfile = '',
        public ?string             $fullName = '',
        public ?string             $description = '',
        public ?string             $slogan = '',
        public ?string             $birthDate = null,
        public ?string             $gender = null,
        public ?string             $country = null,
        public ?string             $language = null,
        public ?string             $city = null,
        #[ApiProperty(example: 'white')]
        public ?string             $label = null,
    ) {
    }

    public static function create(
        AppUserInterface    $user,
    ): self {
        return new self(
            uid: $user->getUid(),
            email: $user->isAnonym() ? '' : $user->getEmail(),
            isVerified: $user->isVerified(),
            isApproved: $user->isApproved(),
            isAI: $user->isAI(),
            status: $user->getStatus(forCurrentUser: true),
            username: $user->getUsername(),
            avatar: $user->getCropAvatarUrl(),
            imageProfile: $user->getImageProfileCropUrl(),
            photoProfile: $user->getPhotoProfileCropUrl(),
            hasImageProfile: $user->hasImageProfile(),
            audioProfile: $user->getAudioProfileUrl(),
            fullName: $user->getFullName(),
            description: $user->getDescription(),
            slogan: $user->getSlogan(),
            birthDate: $user->getInfo()->getBirthDate()?->format('Y-m-d'),
            gender: $user->getInfo()->getSex(),
            country: $user->getInfo()->getCountry(),
            language: $user->getInfo()->getLanguage(),
            city: $user->getInfo()->getCity(),
            label: $user->getLabel(),
        );
    }
}
