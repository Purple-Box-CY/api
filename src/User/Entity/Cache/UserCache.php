<?php

namespace App\User\Entity\Cache;

use App\User\Entity\Interfaces\AppUserInterface as UserInterface;
use App\User\Entity\User;

class UserCache implements UserInterface
{
    public int $id;
    public string $uid;
    public ?string $printName;
    public ?string $username;
    public ?string $avatar;
    public ?string $avatarUrl = null;
    public ?string $avatarOriginalUrl = null;
    public ?string $fullName;
    public string $email;
    public bool $isBlocked;
    public bool $isDeleted;
    public bool $isVerified;
    public string $status;
    public ?string $createdAtFormat = null;

    public static function create(User $user): self
    {
        $userCache = new self();
        $userCache->id = $user->getId();
        $userCache->uid = $user->getUid();
        $userCache->printName = $user->getPrintName();
        $userCache->username = $user->getUsername();
        $userCache->avatar = $user->getAvatar();
        $userCache->avatarUrl = $user->getAvatarUrl();
        $userCache->avatarOriginalUrl = $user->getAvatarOriginalUrl();
        $userCache->fullName = $user->getFullName();
        $userCache->email = $user->getEmail();
        $userCache->isBlocked = $user->isBlocked();
        $userCache->isDeleted = $user->isDeleted();
        $userCache->isVerified = $user->isVerified();
        $userCache->status = $user->getStatus();

        return $userCache;
    }

    public static function createFromCache(object $stdObject): self
    {
        /** @var UserCache $stdObject */
        $userCache = new self();
        $userCache->id = $stdObject->id;
        $userCache->uid = $stdObject->uid;
        $userCache->printName = $stdObject->printName;
        $userCache->username = $stdObject->username;
        $userCache->avatar = $stdObject->avatar;
        $userCache->avatarUrl = $stdObject->avatarUrl;
        $userCache->avatarOriginalUrl = property_exists($stdObject, "avatarOriginalUrl") ? $stdObject->avatarOriginalUrl : null;
        $userCache->fullName = $stdObject->fullName;
        $userCache->email = $stdObject->email;
        $userCache->isBlocked = $stdObject->isBlocked;
        $userCache->isDeleted = $stdObject->isDeleted;
        $userCache->isVerified = $stdObject->isVerified;
        $userCache->status = $stdObject->status;
        $userCache->createdAtFormat = property_exists($stdObject, "createdAtFormat") ? $stdObject->createdAtFormat : null;

        return $userCache;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getUidStr(): string
    {
        return $this->uid;
    }

    public function getPrintName(): ?string
    {
        return $this->printName;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    /**
     * @return string|null
     */
    public function getAvatarOriginalUrl(): ?string
    {
        return $this->avatarOriginalUrl;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }


    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getCreatedAtFormat(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->createdAtFormat;
    }

    public function hasRole(string $role): bool
    {
        return false;
    }

    public function getStatus(bool $forCurrentUser = false): string
    {
        return $this->status;
    }
}
