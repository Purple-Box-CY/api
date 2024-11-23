<?php

namespace App\User\Entity;

use App\Service\Utility\DomainHelper;
use App\User\Repository\UserAvatarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAvatarRepository::class)]
#[ORM\Table(name: "user_avatar")]
class UserAvatar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[OneToOne(inversedBy: "avatarData", targetEntity: User::class)]
    #[JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(nullable: true)]
    private ?string $original = null;

    #[ORM\Column(nullable: true)]
    private ?string $crop = null;

    #[ORM\Column(nullable: true)]
    private ?string $cropBlur = null;

    #[ORM\Column(type: Types::STRING, length: 256, nullable: true)]
    private ?string $declineReason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $declineDescription = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getOriginal(): ?string
    {
        return $this->original;
    }

    public function getOriginalUrl(): ?string
    {
        if (!$this->original) {
            return null;
        }

        return sprintf('%s/%s', DomainHelper::getCdnDomain(), $this->original);
    }

    public function setOriginal(?string $original): self
    {
        $this->original = $original;

        return $this;
    }

    public function getCrop(): ?string
    {
        return $this->crop;
    }

    public function getCropUrl(): ?string
    {
        if (!$this->crop) {
            return null;
        }

        return sprintf('%s/%s', DomainHelper::getCdnDomain(), $this->crop);
    }

    public function setCrop(?string $crop): self
    {
        $this->crop = $crop;

        return $this;
    }

    public function getCropBlur(): ?string
    {
        return $this->cropBlur;
    }

    public function setCropBlur(?string $cropBlur): self
    {
        $this->cropBlur = $cropBlur;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeclineReason(): ?string
    {
        return $this->declineReason;
    }

    /**
     * @param string|null $declineReason
     */
    public function setDeclineReason(?string $declineReason): void
    {
        $this->declineReason = $declineReason;
    }

    /**
     * @return string|null
     */
    public function getDeclineDescription(): ?string
    {
        return $this->declineDescription;
    }

    /**
     * @param string|null $declineDescription
     */
    public function setDeclineDescription(?string $declineDescription): void
    {
        $this->declineDescription = $declineDescription;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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
}
