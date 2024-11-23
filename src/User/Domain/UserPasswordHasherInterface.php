<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\User\Entity\User;

interface UserPasswordHasherInterface
{
    public function hash(User $user, string $password): string;
    public function isPasswordValid(User $user, string $password): bool;
}
