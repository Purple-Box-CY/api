<?php

namespace App\User\DTO\Response\DTO;

use ApiPlatform\Metadata\ApiProperty;
use App\User\Entity\UserDecline;

class UserDeclineDTO
{
    public function __construct(
        #[ApiProperty(example: 'private_social_network_account')]
        public ?string $reason = null,
        #[ApiProperty(example: 'Your social account is private')]
        public ?string $description = null,
    ) {
    }

    public static function create(UserDecline $userDecline): self
    {
        return new self(
            reason: $userDecline->getDeclineReason(),
            description: $userDecline->getDeclineDescription(),
        );
    }
}
