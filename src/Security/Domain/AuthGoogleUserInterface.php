<?php

declare(strict_types=1);

namespace App\Security\Domain;

interface AuthGoogleUserInterface
{
    public function getGoogleId(): ?string;
}
