<?php

namespace App\Security\DTO;

interface JWTUserInterface
{
    public function getId(): int;
    public function getUid(): string;
    public function getUsername(): ?string;
    public function getEmail(): string;
    public function getUlid(): string;
    public function isVerified(): bool;
}