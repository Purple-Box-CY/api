<?php

declare(strict_types=1);

namespace App\Security\Domain;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface AuthUserInterface extends UserInterface, PasswordAuthenticatedUserInterface, AuthGoogleUserInterface
{
    public function getUlid(): string;

    public function getUid(): string;

    public function getId(): int;

    public function getEmail(): string;

    public function getUsername(): ?string;
}
