<?php

namespace App\User\Entity;

use App\User\Repository\UserBlockRepository;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBlockRepository::class)]
#[ORM\Table(name: "user_block")]
class UserBlock
{
    public const REASON_NULL = '';
    public const REASON_INAPPROPRIATE_CONTENT = 'inappropriate_content';
    public const REASON_INTELLECTUAL_PROPERTY_VIOLATIONS = 'intellectual_property_violations';
    public const REASON_HARASSMENT_BULLYING_HATE_SPEECH_DISCRIMINATION = 'harassment_bullying_hate_speech_discrimination';
    public const REASON_SPAM_SCAM = 'spam_scam';
    public const REASONS_DEFAULT_NAME = 'service rules';

    public const REASONS = [
        self::REASON_NULL,
        self::REASON_INAPPROPRIATE_CONTENT,
        self::REASON_INTELLECTUAL_PROPERTY_VIOLATIONS,
        self::REASON_HARASSMENT_BULLYING_HATE_SPEECH_DISCRIMINATION,
        self::REASON_SPAM_SCAM,
    ];

    public const REASONS_NAMES = [
        self::REASON_NULL => '',
        self::REASON_INAPPROPRIATE_CONTENT => 'Inappropriate Content',
        self::REASON_INTELLECTUAL_PROPERTY_VIOLATIONS => 'Intellectual Property Violations',
        self::REASON_HARASSMENT_BULLYING_HATE_SPEECH_DISCRIMINATION => 'Harassment, Bullying, Hate Speech, and Discrimination',
        self::REASON_SPAM_SCAM => 'Spam and Scam',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[OneToOne(inversedBy: "block", targetEntity: User::class)]
    #[JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(nullable: true)]
    private ?string $blockReason = null;


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

    public function getBlockReason(): ?string
    {
        return $this->blockReason;
    }

    public function setBlockReason(?string $blockReason): void
    {
        $this->blockReason = $blockReason;
    }

}
