<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\Exception\Http\BadRequest\NotAcceptableValueException;
use App\Service\Utility\DomainHelper;
use App\User\Entity\Interfaces\AppUserInterface as UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use App\User\Repository\UserRepository;
use App\Security\Domain\AuthUserInterface;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Factory\UlidFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this name')]
#[UniqueEntity(fields: ['google_id'], message: 'There is already an account with this google id')]
#[ORM\Index(columns: ['avatar_status'], name: "avatar_status_filterx")]
#[ORM\Index(columns: ['is_verified'], name: "is_verified_idx")]
class User implements AuthUserInterface, LegacyPasswordAuthenticatedUserInterface, UserInterface
{
    public const ROLE_USER          = 'ROLE_USER';
    public const ROLE_AUTH_TYPE_CMS = 'auth_type_cms';

    public const STATUS_ACTIVE  = 'active';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DELETED = 'deleted';

    public const AVATAR_STATUS_NEW             = 'new';
    public const AVATAR_STATUS_WAITING_APPROVE = 'waiting_approve';
    public const AVATAR_STATUS_ACTIVE          = 'active';
    public const AVATAR_STATUS_BLOCKED         = 'blocked';

    public const SOURCE_GOOGLE = 'google';

    public const AVAILABLE_AVATAR_STATUSES = [
        self::AVATAR_STATUS_NEW,
        self::AVATAR_STATUS_WAITING_APPROVE,
        self::AVATAR_STATUS_ACTIVE,
        self::AVATAR_STATUS_BLOCKED,
    ];

    private const string CONFIG_NOTIFICATIONS_UNSUBSCRIBED = 'notifications_unsubscribed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 1024, unique: true, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $fullName = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 256, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(length: 16, nullable: true)]
    private string $avatarStatus;

    private bool $isJustRegistered = false;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $googleId = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserAvatar::class, cascade: ['persist', 'remove'])]
    private UserAvatar|null $avatarData = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserBlock::class, cascade: ['persist', 'remove'])]
    private UserBlock|null $block = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserDecline::class, cascade: ['persist', 'remove'])]
    private UserDecline|null $decline = null;

    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $ulid;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isBlocked = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = [];

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');

        $this->avatarStatus = self::AVATAR_STATUS_NEW;

        if (!$this->avatarData) {
            $this->avatarData = new UserAvatar();
            $this->avatarData->setUser($this);
        }

        if (!$this->block) {
            $this->block = new UserBlock();
            $this->block->setUser($this);
        }

        if (!$this->decline) {
            $this->decline = new UserDecline();
            $this->decline->setUser($this);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower($email);

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setHashedPassword(string $hashedPassword): self
    {
        $this->password = $hashedPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUlid(): string
    {
        return $this->ulid->toBase32();
    }

    public function getUid(): string
    {
        return (string)$this->ulid;
    }

    public function generateUlid(): self
    {
        $this->ulid = (new UlidFactory())->create();

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): self
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = mb_strtolower($username);

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getAvatarUrl(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        if (str_starts_with($this->avatar, 'http')) { // google avatar
            return $this->avatar;
        }

        return sprintf('%s/%s', DomainHelper::getCdnDomain(), $this->avatar);
    }

    public function getAvatarOriginalUrl(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        if (str_starts_with($this->avatar, 'http')) { // google avatar
            return $this->avatar;
        }

        return $this->getAvatarData()?->getOriginalUrl();
    }


    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getPrintName(): ?string
    {
        return $this->getFullName() ?: $this->getUsername();
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getEmail();
    }


    public function getSalt(): ?string
    {
        return md5('passSalt'.$this->email);
    }

    public function getAvatarData(): ?UserAvatar
    {
        if (!$this->avatarData) {
            $this->avatarData = new UserAvatar();
            $this->avatarData->setUser($this);
        }

        return $this->avatarData;
    }

    public function setAvatarData(?UserAvatar $avatarData): void
    {
        $this->avatarData = $avatarData;
    }

    public function getBlockReason(): ?UserBlock
    {
        if (!$this->block) {
            $this->block = new UserBlock();
            $this->block->setUser($this);
        }

        return $this->block;
    }

    public function setBlockReason(?UserBlock $block): void
    {
        $this->block = $block;
    }

    public function getDeclineReason(): ?string
    {
        if (!$this->decline) {
            $this->decline = new UserDecline();
            $this->decline->setUser($this);
        }

        return $this->decline->getDeclineReason();
    }

    public function getDecline(): ?UserDecline
    {
        return $this->decline;
    }

    public function setDeclineReason(?UserDecline $decline): void
    {
        $this->decline = $decline;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setUlid(Ulid $ulid): self
    {
        $this->ulid = $ulid;

        return $this;
    }


    public function getStatus(bool $forCurrentUser = false): string
    {
        if ($this->isDeleted) {
            return self::STATUS_DELETED;
        }

        if ($this->isBlocked) {
            return self::STATUS_BLOCKED;
        }

        return self::STATUS_ACTIVE;
    }

    public function getAvatarStatus(): ?string
    {
        return $this->avatarStatus;
    }

    public function setAvatarStatus(string $status): self
    {
        if (!in_array($status, self::AVAILABLE_AVATAR_STATUSES)) {
            throw new NotAcceptableValueException();
        }

        $this->avatarStatus = $status;

        return $this;
    }

    public function isJustRegistered(): bool
    {
        return $this->isJustRegistered;
    }

    public function setIsJustRegistered(bool $isJustRegistered): self
    {
        $this->isJustRegistered = $isJustRegistered;

        return $this;
    }

    public function getData(): array
    {
        return $this->data ?? [];
    }

    public function setData(?array $data): self
    {
        $this->data = $data ?? [];

        return $this;
    }

    public function isUnsubscribed(): bool
    {
        return (bool)($this->getData()[self::CONFIG_NOTIFICATIONS_UNSUBSCRIBED] ?? false);
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedAtFormat(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->createdAt?->format($format);
    }


    public function getUidStr(): string
    {
        return (string)$this->ulid;
    }

    public function getUserUrl(): string
    {
        return sprintf('%s/profile/%s', $_ENV['WEB_PROJECT_DOMAIN'], $this->getUidStr());
    }


    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}
