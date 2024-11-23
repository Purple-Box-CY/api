<?php

namespace App\Service\Utility;

use Symfony\Component\Mime\Address;

class ProjectEmailAddressProvider
{
    public function __construct(
        private readonly string $email,
        private readonly string $name,
    )
    {
    }

    public function provide(): Address
    {
        return new Address(address: $this->email, name: $this->name);
    }
}