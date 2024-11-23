<?php

namespace App\User\DTO\Response;

use App\User\Entity\Interfaces\AppUserInterface as UserInterface;

class ResponseUserInfoDTO
{
    public function __construct(
        public string                      $uid,
        public string                      $status,
        public ?string                     $username,
        public ?string                     $avatar,
        public ?string                     $avatarOriginal,
        public ?string                     $fullName,
    ) {
    }

    public static function create(
        UserInterface       $user,
    ): self {
        return new self(
            uid: $user->getUid(),
            status: $user->getStatus(),
            username: $user->getUsername(),
            avatar: $user->getAvatarUrl(),
            avatarOriginal: $user->getAvatarOriginalUrl(),
            fullName: $user->getFullName(),
        );
    }
}
