<?php

namespace App\User\Entity\Interfaces;

interface AppUserInterface
{
    public function getId(): int;

    public function getUid(): string;

    public function getPrintName(): ?string;

    public function getUsername(): ?string;

    public function getAvatar(): ?string;

    public function getAvatarUrl(): ?string;
    public function getAvatarOriginalUrl(): ?string;

    public function getFullName(): ?string;

    public function getEmail(): string;

    public function isBlocked(): bool;

    public function isDeleted(): bool;

    public function isVerified(): bool;

    public function getStatus(bool $forCurrentUser = false): string;

    public function getCreatedAtFormat(string $format = 'Y-m-d H:i:s'): ?string;
    public function hasRole(string $role): bool;
}