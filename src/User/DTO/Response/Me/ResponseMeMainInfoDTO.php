<?php

namespace App\User\DTO\Response\Me;

use App\User\Entity\Interfaces\AppUserInterface;
use App\User\Entity\User;

class ResponseMeMainInfoDTO
{
    public function __construct(
        public string  $uid = '',
        public string  $email = '',
        public bool    $isVerified = false,
        public string  $status = User::STATUS_ACTIVE,
        public ?string $username = '',
        public ?string $avatar = '',
        public ?string $imageProfile = '',
        public ?string $photoProfile = '',
        public bool    $hasImageProfile = false,
        public ?string $fullName = '',
    ) {
    }

    public static function create(
        AppUserInterface $user,
    ): self {
        return new self(
            uid: $user->getUid(),
            email: $user->getEmail(),
            isVerified: $user->isVerified(),
            status: $user->getStatus(forCurrentUser: true),
            username: $user->getUsername(),
            avatar: $user->getAvatarUrl(),
            fullName: $user->getFullName(),
        );
    }
}
