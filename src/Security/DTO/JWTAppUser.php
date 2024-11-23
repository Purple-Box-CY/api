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
    public ?string $imageProfileUrl = null;
    public ?string $photoProfileUrl = null;
    public bool $hasImageProfile = false;
    public ?string $audioProfileUrl = null;
    public ?string $publicAudioProfileUrl = null;
    public ?string $avatarUrl = null;
    public ?string $avatarOriginalUrl = null;
    public ?string $label;
    public ?string $slogan = null;
    public ?string $description = null;
    public ?string $googleId;
    public ?string $password;
    public bool $isVerified;
    public bool $isBlocked;
    public bool $isDeleted;
    public bool $isApproved;
    public bool $isAnonym;
    public bool $isAdmin = false;
    public bool $isAI = false;
    public bool $isPaying = false;
    public string $status;
    public string $statusForCurrentUser;
    //public string $roles = '';
    public array $roles = [];
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
        $userCache->username = $user->getUsername();
        $userCache->printName = $user->getPrintName();
        $userCache->fullName = $user->getFullName();
        $userCache->email = $user->getEmail();
        $userCache->googleId = $user->getGoogleId();
        $userCache->password = $user->getPassword();
        $userCache->avatar = $user->getAvatar();
        $userCache->avatarUrl = $user->getAvatarUrl();
        $userCache->avatarOriginalUrl = $user->getAvatarOriginalUrl();
        $userCache->imageProfileUrl = $user->getImageProfileUrl();
        $userCache->photoProfileUrl = $user->getPhotoProfileUrl();
        $userCache->hasImageProfile = $user->hasImageProfile();
        $userCache->audioProfileUrl = $user->getAudioProfileUrl();
        $userCache->publicAudioProfileUrl = $user->getPublicAudioProfileUrl();
        $userCache->label = $user->getLabel();
        $userCache->slogan = $user->getSlogan();
        $userCache->description = $user->getDescription();
        $userCache->isVerified = $user->isVerified();
        $userCache->isBlocked = $user->isBlocked();
        $userCache->isDeleted = $user->isDeleted();
        $userCache->isApproved = $user->isApproved();
        $userCache->isAnonym = $user->isAnonym();
        $userCache->isAdmin = $user->isAdmin();
        $userCache->isAI = $user->isAI();
        $userCache->isPaying = $user->isPaying();
        $userCache->status = $user->getStatus();
        $userCache->statusForCurrentUser = $user->getStatus(true);
        $userCache->roles = $user->getRoles();
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
        $userCache->imageProfileUrl = property_exists($stdObject, "imageProfileUrl") ? $stdObject->imageProfileUrl : User::getImageProfileCover((int)$stdObject->id);
        $userCache->photoProfileUrl = property_exists($stdObject, "photoProfileUrl") ? $stdObject->photoProfileUrl : null;
        $userCache->hasImageProfile = property_exists($stdObject, "hasImageProfile") ? (bool)$stdObject->hasImageProfile : false;
        $userCache->audioProfileUrl = property_exists($stdObject, "audioProfileUrl") ? $stdObject->audioProfileUrl : null;
        $userCache->publicAudioProfileUrl = property_exists($stdObject, "publicAudioProfileUrl") ? $stdObject->publicAudioProfileUrl : null;
        $userCache->label = $stdObject->label;
        $userCache->slogan = property_exists($stdObject, "slogan") ? $stdObject->slogan : null;;
        $userCache->description = property_exists($stdObject, "description") ? $stdObject->description : null;;
        $userCache->googleId = $stdObject->googleId;
        $userCache->password = $stdObject->password;
        $userCache->isVerified = $stdObject->isVerified;
        $userCache->roles = $stdObject->roles;
        $userCache->isBlocked = $stdObject->isBlocked;
        $userCache->isDeleted = $stdObject->isDeleted;
        $userCache->isApproved = $stdObject->isApproved;
        $userCache->isAnonym = property_exists($stdObject, "isAnonym") ? $stdObject->isAnonym : false;
        $userCache->isAdmin = property_exists($stdObject, "isAdmin") ? $stdObject->isAdmin : false;
        $userCache->isAI = property_exists($stdObject, "isAI") ? $stdObject->isAI : false;
        $userCache->isPaying = property_exists($stdObject, "isPaying") ? $stdObject->isPaying : false;
        $userCache->status = $stdObject->status;
        $userCache->statusForCurrentUser = $stdObject->statusForCurrentUser;
        $userCache->createdAtFormat = property_exists($stdObject, "createdAtFormat") ? $stdObject->createdAtFormat : null;
        $userCache->lastPaymentAtFormat = property_exists($stdObject, "lastPaymentAtFormat") ? $stdObject->lastPaymentAtFormat : null;
        $userCache->paymentsTotal = property_exists($stdObject, "paymentsTotal") ? $stdObject->paymentsTotal : null;
        $userCache->getFollowingCount = property_exists($stdObject, "getFollowingCount") ? $stdObject->getFollowingCount : 0;
        $userCache->getFollowersCount = property_exists($stdObject, "getFollowersCount") ? $stdObject->getFollowersCount : 0;
        $userCache->getViewersCount = property_exists($stdObject, "getViewersCount") ? $stdObject->getViewersCount : 0;
        $userCache->isPWA = property_exists($stdObject, "isPWA") ? $stdObject->isPWA : false;

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
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
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
     * @return bool
     */
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

    /**
     * @return string
     */
    public function getStatus(bool $forCurrentUser = false): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusForCurrentUser(): string
    {
        return $this->statusForCurrentUser;
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
        return $this->getFollowingCount;
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
        return in_array($role, $this->roles);
    }
}
