<?php

namespace App\User\Entity;

use App\User\Repository\UserDeclineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDeclineRepository::class)]
#[ORM\Table(name: "user_decline")]
class UserDecline
{
    public const REASON_DEFAULT = 'Verification conditions not met';
    public const REASON_NULL = '';
    public const REASON_OTHER = 'other';

    public const REASONS = [
        self::REASON_NULL,
        self::REASON_OTHER,
    ];

    public const REASONS_NAMES = [
        self::REASON_NULL => '',
        self::REASON_OTHER => 'Other',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[OneToOne(inversedBy: "decline", targetEntity: User::class)]
    #[JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true)]
    private ?string $declineReason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $declineDescription = null;


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

    public function getDeclineReason(): ?string
    {
        return $this->declineReason;
    }

    public function setDeclineReason(?string $declineReason): void
    {
        $this->declineReason = $declineReason;
    }

    public function getDeclineDescription(): ?string
    {
        return $this->declineDescription;
    }

    public function setDeclineDescription(?string $declineDescription): void
    {
        $this->declineDescription = $declineDescription;
    }
}
