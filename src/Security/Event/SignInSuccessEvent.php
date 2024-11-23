<?php

declare(strict_types=1);

namespace App\Security\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class SignInSuccessEvent
{
    public const string NAME = 'app.user.sign_in_success';

    public function __construct(private readonly UserInterface $user, private readonly Request $request)
    {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}

