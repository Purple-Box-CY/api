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
    public ?string $imageProfileUrl = null;
    public ?string $photoProfileUrl = null;
    public bool $hasImageProfile = false;
    public ?string $audioProfileUrl = null;
    public ?string $publicAudioProfileUrl = null;
    public ?string $label;
    public ?string $fullName;
    public string $email;
    public bool $isBlocked;
    public bool $isDeleted;
    public bool $isApproved;
    public bool $isVerified;
    public bool $isAnonym = false;
    public bool $isAdmin = false;
    public bool $isAI = false;
    public bool $isPaying = false;
    public string $status;
    public string $statusForCurrentUser;
    public ?string $slogan;
    public ?string $description = null;
    public ?string $createdAtFormat = null;
    public ?string $lastPaymentAtFormat = null;
    public ?float $paymentsTotal = null;
    public int $getFollowersCount = 0;
    public int $getFollowingCount = 0;
    public int $getViewersCount = 0;
    public bool $isPWA = false;

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
        $userCache->imageProfileUrl = $user->getImageProfileUrl();
        $userCache->photoProfileUrl = $user->getPhotoProfileUrl();
        $userCache->hasImageProfile = $user->hasImageProfile();
        $userCache->audioProfileUrl = $user->getAudioProfileUrl();
        $userCache->publicAudioProfileUrl = $user->getPublicAudioProfileUrl();
        $userCache->label = $user->getLabel();
        $userCache->fullName = $user->getFullName();
        $userCache->email = $user->getEmail();
        $userCache->isBlocked = $user->isBlocked();
        $userCache->isDeleted = $user->isDeleted();
        $userCache->isApproved = $user->isApproved();
        $userCache->isVerified = $user->isVerified();
        $userCache->isAnonym = $user->isAnonym();
        $userCache->isAdmin = $user->isAdmin();
        $userCache->isAI = $user->isAI();
        $userCache->isPaying = $user->isPaying();
        $userCache->status = $user->getStatus();
        $userCache->statusForCurrentUser = $user->getStatus(true);
        $userCache->slogan = $user->getSlogan();
        $userCache->description = $user->getDescription();
        $userCache->createdAtFormat = $user->getCreatedAtFormat();
        $userCache->lastPaymentAtFormat = $user->getLastPaymentAtFormat();
        $userCache->paymentsTotal = $user->getPaymentsTotal();
        $userCache->getFollowingCount = $user->getFollowingCount();
        $userCache->getFollowersCount = $user->getFollowersCount();
        $userCache->getViewersCount = $user->getViewersCount();
        $userCache->isPWA = $user->isPWA();

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
        $userCache->imageProfileUrl = property_exists($stdObject, "imageProfileUrl") ? $stdObject->imageProfileUrl : User::getImageProfileCover((int)$stdObject->id);
        $userCache->photoProfileUrl = property_exists($stdObject, "photoProfileUrl") ? $stdObject->photoProfileUrl : null;
        $userCache->hasImageProfile = property_exists($stdObject, "hasImageProfile") ? (bool)$stdObject->hasImageProfile : false;
        $userCache->audioProfileUrl = property_exists($stdObject, "audioProfileUrl") ? $stdObject->audioProfileUrl : null;
        $userCache->publicAudioProfileUrl = property_exists($stdObject, "publicAudioProfileUrl") ? $stdObject->publicAudioProfileUrl : null;
        $userCache->label = $stdObject->label;
        $userCache->fullName = $stdObject->fullName;
        $userCache->email = $stdObject->email;
        $userCache->isBlocked = $stdObject->isBlocked;
        $userCache->isDeleted = $stdObject->isDeleted;
        $userCache->isApproved = $stdObject->isApproved;
        $userCache->isVerified = $stdObject->isVerified;
        $userCache->isAnonym = property_exists($stdObject, "isAnonym") ? $stdObject->isAnonym : false;
        $userCache->isAdmin = property_exists($stdObject, "isAdmin") ? $stdObject->isAdmin : false;
        $userCache->isAI = property_exists($stdObject, "isAI") ? $stdObject->isAI : false;
        $userCache->isPaying = property_exists($stdObject, "isPaying") ? $stdObject->isPaying : false;
        $userCache->status = $stdObject->status;
        $userCache->statusForCurrentUser = $stdObject->statusForCurrentUser;
        $userCache->slogan = $stdObject->slogan;
        $userCache->description = property_exists($stdObject, "description") ? $stdObject->description : null;
        $userCache->createdAtFormat = property_exists($stdObject, "createdAtFormat") ? $stdObject->createdAtFormat : null;
        $userCache->lastPaymentAtFormat = property_exists($stdObject, "lastPaymentAtFormat") ? $stdObject->lastPaymentAtFormat : null;
        $userCache->paymentsTotal = property_exists($stdObject, "paymentsTotal") ? $stdObject->paymentsTotal : null;
        $userCache->getFollowingCount = property_exists($stdObject, "getFollowingCount") ? $stdObject->getFollowingCount : 0;
        $userCache->getFollowersCount = property_exists($stdObject, "getFollowersCount") ? $stdObject->getFollowersCount : 0;
        $userCache->getViewersCount = property_exists($stdObject, "getViewersCount") ? $stdObject->getViewersCount : 0;
        $userCache->isPWA = property_exists($stdObject, "isPWA") ? $stdObject->isPWA : false;

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

    public function getImageProfileUrl(): ?string
    {
        return $this->imageProfileUrl;
    }

    public function getPhotoProfileUrl(): ?string
    {
        return $this->photoProfileUrl;
    }

    public function hasImageProfile(): bool
    {
        return $this->hasImageProfile;
    }

    public function getLabel(): ?string
    {
        return $this->label;
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

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    /**
     * @return bool
     */
    public function isModel(): bool
    {
        return $this->isApproved;
    }

    /**
     * @return bool
     */
    public function isFan(): bool
    {
        return !$this->isApproved;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function isAnonym(): bool
    {
        return $this->isAnonym;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function isAI(): bool
    {
        return $this->isAI;
    }

    public function isPaying(): bool
    {
        return $this->isPaying;
    }

    public function getStatus(bool $forCurrentUser = false): string
    {
        if ($forCurrentUser) {
            return $this->statusForCurrentUser;
        }

        return $this->status;
    }

    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isHasImageProfile(): bool
    {
        return $this->hasImageProfile;
    }

    /**
     * @return string|null
     */
    public function getAudioProfileUrl(): ?string
    {
        return $this->audioProfileUrl;
    }

    /**
     * @return string|null
     */
    public function getPublicAudioProfileUrl(): ?string
    {
        return $this->publicAudioProfileUrl;
    }

    /**
     * @return string
     */
    public function getStatusForCurrentUser(): string
    {
        return $this->statusForCurrentUser;
    }

    public function getCreatedAtFormat(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->createdAtFormat;
    }

    public function getLastPaymentAtFormat(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->lastPaymentAtFormat;
    }

    public function getPaymentsTotal(): ?float
    {
        return $this->paymentsTotal;
    }

    public function getFollowingCount(): int
    {
        return $this->getFollowingCount;
    }

    public function getFollowersCount(): int
    {
        return $this->getFollowersCount;
    }

    public function getViewersCount(): int
    {
        return $this->getViewersCount;
    }

    public function isPWA(): bool
    {
        return $this->isPWA;
    }

    public function hasRole(string $role): bool
    {
        return false;
    }
}
