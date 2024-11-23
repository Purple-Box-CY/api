<?php

namespace App\Security\DTO;

use App\Security\Domain\AuthUserInterface;
use App\User\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use App\User\Entity\Interfaces\AppUserInterface;

class JWTAppUser implements UserInterface, AuthUserInterface, JWTUserInterface, AppUserInterface
{
    public int $id;
    public string $uid;
    public string $email;
    public ?string $username;
    public ?string $printName;
    public ?string $fullName;
    public ?string $avatar;
    public ?string $avatarUrl = null;
    public ?string $avatarOriginalUrl = null;
    public ?string $googleId;
    public ?string $password;
    public bool $isVerified;
    public bool $isBlocked;
    public bool $isDeleted;
    public string $status;
    public array $roles = [];
    public ?string $createdAtFormat = null;

    public static function create(User $user): self
    {
        $userCache = new self();
        $userCache->id = $user->getId();
        $userCache->uid = $user->getUid();
        $userCache->username = $user->getUsername();
        $userCache->printName = $user->getPrintName();
        $userCache->fullName = $user->getFullName();
        $userCache->email = $user->getEmail();
        $userCache->googleId = $user->getGoogleId();
        $userCache->password = $user->getPassword();
        $userCache->avatar = $user->getAvatar();
        $userCache->avatarUrl = $user->getAvatarUrl();
        $userCache->avatarOriginalUrl = $user->getAvatarOriginalUrl();
        $userCache->isVerified = $user->isVerified();
        $userCache->isBlocked = $user->isBlocked();
        $userCache->isDeleted = $user->isDeleted();
        $userCache->status = $user->getStatus();
        $userCache->roles = $user->getRoles();
        $userCache->createdAtFormat = $user->getCreatedAtFormat();

        return $userCache;
    }

    public static function createFromCache(object $stdObject): self
    {
        $userCache = new self();
        $userCache->id = $stdObject->id;
        $userCache->uid = $stdObject->uid;
        $userCache->username = $stdObject->username;
        $userCache->printName = $stdObject->printName;
        $userCache->fullName = $stdObject->fullName;
        $userCache->email = $stdObject->email;
        $userCache->avatar = $stdObject->avatar;
        $userCache->avatarUrl = $stdObject->avatarUrl;
        $userCache->avatarOriginalUrl = property_exists($stdObject, "avatarOriginalUrl") ? $stdObject->avatarOriginalUrl : null;
        $userCache->googleId = $stdObject->googleId;
        $userCache->password = $stdObject->password;
        $userCache->isVerified = $stdObject->isVerified;
        $userCache->roles = $stdObject->roles;
        $userCache->isBlocked = $stdObject->isBlocked;
        $userCache->isDeleted = $stdObject->isDeleted;
        $userCache->status = $stdObject->status;
        $userCache->createdAtFormat = property_exists($stdObject, "createdAtFormat") ? $stdObject->createdAtFormat : null;

        if (in_array(User::ROLE_AUTH_TYPE_CMS, $userCache->roles)) {
            $userCache->roles = array_diff($userCache->roles, [User::ROLE_AUTH_TYPE_CMS]);
        }

        return $userCache;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
        //return explode(',',$this->roles);
    }


    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUlid(): string
    {
        return $this->uid;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @return string|null
     */
    public function getPrintName(): ?string
    {
        return $this->printName;
    }

    /**
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @return string|null
     */
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

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @return string
     */
    public function getStatus(bool $forCurrentUser = false): string
    {
        return $this->status;
    }


    public function getCreatedAtFormat(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->createdAtFormat;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}
