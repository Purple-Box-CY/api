<?php

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Event\CreateUserEvent;
use App\User\Event\UserUpdateAvatarEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserEventService
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function sendEventCreateUser(User $user): void
    {
        $this->dispatcher->dispatch(new CreateUserEvent($user), CreateUserEvent::NAME);
    }

    public function sendEventUserUpdateAvatar(User $user): void
    {
        $this->dispatcher->dispatch(new UserUpdateAvatarEvent($user), UserUpdateAvatarEvent::NAME);
    }

}