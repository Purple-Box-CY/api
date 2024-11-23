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
        public string $status = User::STATUS_ACTIVE,
        public ?string $username = '',
        public ?string $avatar = '',
        public ?string $fullName = '',
        #[ApiProperty(example: 'white')]
        public ?UserDeclineDTO $decline = null,
    ) {
    }

    public static function create(User $user,): self
    {
        $data = new self(
            uid: $user->getUid(),
            email: $user->getEmail(),
            isVerified: $user->isVerified(),
            status: $user->getStatus(forCurrentUser: true),
            username: $user->getUsername(),
            avatar: $user->getAvatarUrl(),
            fullName: $user->getFullName(),
            decline: $user->getDeclineReason() ? UserDeclineDTO::create($user->getDecline()) : null,
        );

        return $data;
    }
}
