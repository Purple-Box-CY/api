<?php

namespace App\User\Domain;

use App\User\Entity\User;

class UserFactory
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function create(
        string  $email,
        string  $username,
        ?string $fullName = null,
        ?string $password = null,
    ): User {
        $user = new User();
        $user->setEmail($email)
            ->setUsername($username);

        if ($fullName) {
            $user->setFullName($fullName);
        }

        if ($password) {
            $user->setPassword(
                $this->passwordHasher->hash($user, $password),
            );
        }
        $user->setRoles(['ROLE_USER']);
        $user->generateUlid();

        return $user;
    }

    public function update(
        User    $user,
        string  $email,
        string  $username,
        ?string $fullName = null,
        ?string $password = null
    ): User {
        $user->setEmail($email)
            ->setUsername($username);

        if ($fullName) {
            $user->setFullName($fullName);
        }

        if ($password) {
            $user->setPassword(
                $this->passwordHasher->hash($user, $password),
            );
        }

        return $user;
    }

    public function changePassword(User $user, $newPassword): User
    {
        $user->setPassword(
            $this->passwordHasher->hash($user, $newPassword),
        );

        return $user;
    }
}
