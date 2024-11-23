<?php

declare(strict_types=1);

namespace App\User\Event;

use App\User\Entity\User;
use App\User\Event\Interfaces\UserEventInterface;

class CreateUserEvent implements UserEventInterface
{
    public const NAME = 'app.user.create';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}

