<?php

namespace App\User\Entity\Interfaces;

interface AppUserInterface
{
    public function getId(): int;

    public function getUid(): string;

    public function getPrintName(): ?string;

    public function getUsername(): ?string;

    public function getAvatar(): ?string;

    public function getImageProfileUrl(): ?string;
    public function getPhotoProfileUrl(): ?string;

    public function hasImageProfile(): bool;

    public function getAudioProfileUrl(): ?string;

    public function getPublicAudioProfileUrl(): ?string;

    public function getAvatarUrl(): ?string;
    public function getAvatarOriginalUrl(): ?string;

    public function getLabel(): ?string;
    public function getSlogan(): ?string;
    public function getDescription(): ?string;

    public function getFullName(): ?string;

    public function getEmail(): string;

    public function isBlocked(): bool;

    public function isDeleted(): bool;

    public function isApproved(): bool;
    public function isModel(): bool;
    public function isFan(): bool;

    public function isVerified(): bool;

    public function isAnonym(): bool;
    public function isAdmin(): bool;
    public function isAI(): bool;
    public function isPaying(): ?bool;

    public function getStatus(bool $forCurrentUser = false): string;

    public function getCreatedAtFormat(string $format = 'Y-m-d H:i:s'): ?string;
    public function getLastPaymentAtFormat(string $format = 'Y-m-d H:i:s'): ?string;
    public function getPaymentsTotal(): ?float;

    public function getFollowingCount(): int;

    public function getFollowersCount(): int;

    public function getViewersCount(): int;

    public function isPWA(): bool;
    public function hasRole(string $role): bool;
}