<?php

namespace App\ApiDTO\Response\User;

use ApiPlatform\Metadata\ApiProperty;
use App\User\Entity\Interfaces\AppUserInterface as UserInterface;

class ResponseUserDTO
{
    public function __construct(
        #[ApiProperty(example: '01HE50RJ1T323D6DKQ6RQ8Y67E')]
        public string $uid,

        #[ApiProperty(example: 'Bart Simpson')]
        public ?string $name,

        #[ApiProperty(example: 'bartholomew')]
        public string $username,

        #[ApiProperty(example: 'https://static.wikia.nocookie.net/simpsons/images/d/d1/Catch_Phrase.jpg/revision/latest/scale-to-width-down/140?cb=20101120000155')]
        public ?string $avatar,
    ) {
    }

    public static function create(
        UserInterface $user,
    ): self
    {
        return new self(
            uid: $user->getUid(),
            name: $user->getPrintName(),
            username: $user->getUsername(),
            avatar: $user->getAvatarUrl(),
        );
    }

}
