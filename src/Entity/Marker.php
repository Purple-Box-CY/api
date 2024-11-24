<?php

namespace App\Entity;

use App\Repository\MarkerRepository;
use App\Service\Utility\MomentHelper;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: MarkerRepository::class)]
#[ORM\Table(name: "markers")]
class Marker
{
    public const TYPE_PAPER      = 'paper';
    public const TYPE_GLASS      = 'glass';
    public const TYPE_PLASTIC    = 'plastic';
    public const TYPE_CLOTH      = 'cloth';
    public const TYPE_ELECTRONIC = 'electronic';
    public const TYPE_BATTERY    = 'battery';
    public const TYPE_GREENPOINT = 'green_point';
    public const TYPE_MULTIBOX   = 'multibox';

    public const AVAILABLE_TYPES = [
        self::TYPE_PAPER,
        self::TYPE_GLASS,
        self::TYPE_PLASTIC,
        self::TYPE_CLOTH,
        self::TYPE_ELECTRONIC,
        self::TYPE_BATTERY,
        self::TYPE_GREENPOINT,
        self::TYPE_MULTIBOX,
    ];

    public const STATUS_NEW             = 'new';
    public const STATUS_WAITING_APPROVE = 'waiting_approve';
    public const STATUS_ACTIVE          = 'active';
    public const STATUS_ARCHIVE         = 'archive';
    public const STATUS_DELETED         = 'deleted';
    public const STATUS_BLOCKED         = 'blocked';

    public const AVAILABLE_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_WAITING_APPROVE,
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVE,
        self::STATUS_DELETED,
        self::STATUS_BLOCKED,
    ];

    public const STATUSES_FOR_OWNER = [
        self::STATUS_NEW,
        self::STATUS_WAITING_APPROVE,
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVE,
        self::STATUS_BLOCKED,
    ];

    public const STATUSES_FOR_SHOW = [
        self::STATUS_ACTIVE,
    ];

    private const LABEL_NEW = 'new';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $uid;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "SET NULL")]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 256, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING, length: 32, nullable: false)]
    private string $latitude;

    #[ORM\Column(type: Types::STRING, length: 32, nullable: false)]
    private string $longitude;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 256, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column]
    private bool $isActive = false;

    #[ORM\Column]
    private bool $isPaper = false;

    #[ORM\Column]
    private bool $isGlass = false;

    #[ORM\Column]
    private bool $isPlastic = false;

    #[ORM\Column]
    private bool $isCloth = false;

    #[ORM\Column]
    private bool $isElectronic = false;

    #[ORM\Column]
    private bool $isBattery = false;

    #[ORM\Column]
    private bool $isGreenPoint = false;

    #[ORM\Column]
    private bool $isMultibox = false;

    #[ORM\Column(length: 16)]
    private string $status;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    private function __construct()
    {
        $this->uid = new Ulid();
        $this->createdAt = new \DateTime('now');
        $this->status = self::STATUS_NEW;
    }

    public static function create(
        User    $user,
        ?string $type = null,
        ?string $name = null,
        ?string $description = null,
    ): static {
        $post = new Marker();
        $post
            ->setUser($user)
            ->setType($type)
            ->setDescription($description);

        return $post;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUid(): ?Ulid
    {
        return $this->uid;
    }

    public function getUidStr(): string
    {
        return (string)$this->uid;
    }

    public function setUid(Ulid $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->isActive = ($status == self::STATUS_ACTIVE);

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function isArchive(): bool
    {
        return $this->status === self::STATUS_ARCHIVE;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAtFormat(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->createdAt->format($format);
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function isFresh(): bool
    {
        return (time() - $this->createdAt->getTimestamp()) < MomentHelper::SECONDS_2_DAYS;
    }

    public function getLabel(): ?string
    {
        if ($this->isFresh()) {
            return self::LABEL_NEW;
        }

        return null;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Marker
    {
        $this->name = $name;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): Marker
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function isPaper(): bool
    {
        return $this->isPaper;
    }

    public function setIsPaper(bool $isPaper): Marker
    {
        $this->isPaper = $isPaper;

        return $this;
    }

    public function isGlass(): bool
    {
        return $this->isGlass;
    }

    public function setIsGlass(bool $isGlass): Marker
    {
        $this->isGlass = $isGlass;

        return $this;
    }

    public function isPlastic(): bool
    {
        return $this->isPlastic;
    }

    public function setIsPlastic(bool $isPlastic): Marker
    {
        $this->isPlastic = $isPlastic;

        return $this;
    }

    public function isCloth(): bool
    {
        return $this->isCloth;
    }

    public function setIsCloth(bool $isCloth): Marker
    {
        $this->isCloth = $isCloth;

        return $this;
    }

    public function isElectronic(): bool
    {
        return $this->isElectronic;
    }

    public function setIsElectronic(bool $isElectronic): Marker
    {
        $this->isElectronic = $isElectronic;

        return $this;
    }

    public function isBattery(): bool
    {
        return $this->isBattery;
    }

    public function setIsBattery(bool $isBattery): Marker
    {
        $this->isBattery = $isBattery;

        return $this;
    }

    public function isGreenPoint(): bool
    {
        return $this->isGreenPoint;
    }

    public function setIsGreenPoint(bool $isGreenPoint): Marker
    {
        $this->isGreenPoint = $isGreenPoint;

        return $this;
    }

    public function isMultibox(): bool
    {
        return $this->isMultibox;
    }

    public function setIsMultibox(bool $isMultibox): Marker
    {
        $this->isMultibox = $isMultibox;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): Marker
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLat(): float
    {
        return (float)$this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function getLng(): float
    {
        return (float)$this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }
}
