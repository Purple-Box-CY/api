<?php

namespace App\User\Entity;

use App\User\Repository\UserInfoRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

#[ORM\Entity(repositoryClass: UserInfoRepository::class)]
#[ORM\Table(name: "user_info")]
class UserInfo
{
    private const string CONFIG_AI_CHAT                    = 'ai_chat';
    private const string CONFIG_AI_PROFILE                 = 'ai_profile';
    private const string CONFIG_NOTIFICATIONS_UNSUBSCRIBED = 'notifications_unsubscribed';
    private const string CONFIG_REG_POINT                  = 'reg_point';
    private const string CONFIG_REG_SOURCE                 = 'reg_source';
    private const string CONFIG_REG_FROM_USER              = 'reg_from_user_uid';
    private const string CONFIG_IS_ADMIN                   = 'is_admin';
    private const string CONFIG_IS_AI                      = 'is_ai';
    private const string SLOGAN                            = 'slogan';
    private const string CONFIG_PAYING_CATEGORY            = 'paying_category';
    private const string CONFIG_HAS_CHAT_AUTO_MESSAGING    = 'has_chat_auto_messaging';
    private const string CONFIG_DEVICE_ID                  = 'device_id';
    private const string CONFIG_MIRROR_USER_ID             = 'mirror_user_id';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[OneToOne(inversedBy: "info", targetEntity: User::class)]
    #[JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?string $voiceDescription = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(nullable: true)]
    private ?string $sex = null;

    #[ORM\Column(nullable: true)]
    private ?string $country = null;

    #[ORM\Column(nullable: true)]
    private ?string $language = null;

    #[ORM\Column(nullable: true)]
    private ?string $city = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getVoiceDescription(): ?string
    {
        return $this->voiceDescription;
    }

    public function setVoiceDescription(?string $voiceDescription): void
    {
        $this->voiceDescription = $voiceDescription;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeImmutable $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(?string $sex): void
    {
        $this->sex = $sex;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }


    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
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

    public function isAiChat(): bool
    {
        return (bool)($this->getData()[self::CONFIG_AI_CHAT] ?? false);
    }

    public function isUnsubscribed(): bool
    {
        return (bool)($this->getData()[self::CONFIG_NOTIFICATIONS_UNSUBSCRIBED] ?? false);
    }

    public function setUnsubscribed(bool $value): self
    {
        $data = $this->getData();
        $data[self::CONFIG_NOTIFICATIONS_UNSUBSCRIBED] = $value;
        $this->setData($data);

        return $this;
    }

    public function setRegPoint(?string $regPoint): self
    {
        $data = $this->getData();
        $data[self::CONFIG_REG_POINT] = $regPoint;
        $this->setData($data);

        return $this;
    }

    public function getRegPoint(): string
    {
        return $this->getData()[self::CONFIG_REG_POINT] ?? '';
    }

    public function setRegSource(?string $regSource): self
    {
        $data = $this->getData();
        $data[self::CONFIG_REG_SOURCE] = $regSource;
        $this->setData($data);

        return $this;
    }

    public function getRegSource(): string
    {
        return $this->getData()[self::CONFIG_REG_SOURCE] ?? '';
    }

    public function setRegFromUserUid(?string $userUid): self
    {
        $data = $this->getData();
        $data[self::CONFIG_REG_FROM_USER] = $userUid;
        $this->setData($data);

        return $this;
    }

    public function getRegFromUserUid(): ?string
    {
        return $this->getData()[self::CONFIG_REG_FROM_USER] ?? null;
    }

    public function setIsAiChat(bool $isAiChat): self
    {
        $data = $this->getData();
        $data[self::CONFIG_AI_CHAT] = $isAiChat;
        $this->setData($data);

        return $this;
    }

    public function setIsAiProfile(string $aiProfile): self
    {
        $data = $this->getData();
        $data[self::CONFIG_AI_PROFILE] = $aiProfile;
        $this->setData($data);

        return $this;
    }

    public function getAiProfile(): ?string
    {
        return $this->getData()[self::CONFIG_AI_PROFILE] ?? null;
    }

    public function getSlogan(): ?string
    {
        return $this->getData()[self::SLOGAN] ?? null;
    }

    public function setSlogan(string $slogan): self
    {
        $data = $this->getData();
        $data[self::SLOGAN] = $slogan;
        $this->setData($data);

        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->getData()[self::CONFIG_IS_ADMIN] ?? false;
    }

    public function isAI(): bool
    {
        return $this->getData()[self::CONFIG_IS_AI] ?? false;
    }

    public function getPayingCategory(): ?int
    {
        return $this->getData()[self::CONFIG_PAYING_CATEGORY] ?? null;
    }

    public function setPayingCategory(int $category): self
    {
        $data = $this->getData();
        $data[self::CONFIG_PAYING_CATEGORY] = $category;
        $this->setData($data);

        return $this;
    }

    public function getDeviceID(): ?string
    {
        return $this->getData()[self::CONFIG_DEVICE_ID] ?? null;
    }

    public function setDeviceID(string $deviceID): self
    {
        $data = $this->getData();
        $data[self::CONFIG_DEVICE_ID] = $deviceID;
        $this->setData($data);

        return $this;
    }

    public function setHasChatAutoMessaging(bool $value): self
    {
        $data = $this->getData();
        $data[self::CONFIG_HAS_CHAT_AUTO_MESSAGING] = $value;
        $this->setData($data);

        return $this;
    }

    public function hasChatAutoMessaging(): bool
    {
        return (bool)($this->getData()[self::CONFIG_HAS_CHAT_AUTO_MESSAGING] ?? false);
    }


    public function getMirrorUserId(): ?string
    {
        return $this->getData()[self::CONFIG_MIRROR_USER_ID] ?? null;
    }

    public function setMirrorUserId(string $mirrorUserId): self
    {
        $data = $this->getData();
        $data[self::CONFIG_MIRROR_USER_ID] = $mirrorUserId;
        $this->setData($data);

        return $this;
    }
}
