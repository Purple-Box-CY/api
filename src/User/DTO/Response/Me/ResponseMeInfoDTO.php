<?php

namespace App\User\DTO\Response\Me;

use ApiPlatform\Metadata\ApiProperty;
use App\User\DTO\Response\DTO\UserDeclineDTO;
use App\User\Entity\User;

class ResponseMeInfoDTO
{
    public function __construct(
        public string $uid = '',
        public string $email = '',
        public bool $isVerified = false,
        public bool $isApproved = false,
        public bool $isAnonym = false,
        public ?bool $isAdmin = null,
        public string $status = User::STATUS_ACTIVE,
        public ?string $username = '',
        public ?string $avatar = '',
        public ?string $fullName = '',
        public ?string $country = null,
        public ?string $language = null,
        public int $notificationCount = 0,
        #[ApiProperty(example: 'white')]
        public ?string $label = null,
        public ?UserDeclineDTO $decline = null,
        public bool $isPWA = false,
    ) {
    }

    public static function create(User $user,): self
    {
        $data = new self(
            uid: $user->getUid(),
            email: $user->isAnonym() ? '' : $user->getEmail(),
            isVerified: $user->isVerified(),
            isApproved: $user->isApproved(),
            isAnonym: $user->isAnonym(),
            status: $user->getStatus(forCurrentUser: true),
            username: $user->getUsername(),
            avatar: $user->getAvatarUrl(),
            fullName: $user->getFullName(),
            country: $user->getCountry(),
            language: $user->getInfo()->getLanguage(),
            label: $user->getLabel(),
            decline: $user->getDeclineReason() ? UserDeclineDTO::create($user->getDecline()) : null,
        );

        if ($user->isAdmin()) {
            $data->isAdmin = true;
        }

        return $data;
    }
}
