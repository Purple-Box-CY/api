<?php

namespace App\User\Entity;

use App\Service\Utility\DomainHelper;
use App\User\Repository\UserPhotoProfileRepository;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPhotoProfileRepository::class)]
#[ORM\Table(name: "user_photo_profile")]
class UserPhotoProfile
{
    public const IMAGE_PROFILE_CROP_WIDTH = 750;
    public const IMAGE_PROFILE_CROP_HEIGHT = 660;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[OneToOne(inversedBy: "photoProfileData", targetEntity: User::class)]
    #[JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(nullable: true)]
    private ?string $original = null;

    #[ORM\Column(nullable: true)]
    private ?string $crop = null;

    #[ORM\Column(nullable: true)]
    private ?string $cropBlur = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = [];

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

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}
