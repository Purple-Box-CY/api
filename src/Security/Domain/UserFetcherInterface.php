<?php

declare(strict_types=1);

namespace App\Security\Domain;

use App\User\Entity\Interfaces\AppUserInterface;

interface UserFetcherInterface
{
    public function getAuthUser(): AuthUserInterface;
    public function getCurrentUser(): ?AppUserInterface;
}
