<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\Entity\FirebaseToken;
use App\Entity\Follow;
use App\Entity\Team;
use App\Entity\View\View;
use App\Exception\Http\BadRequest\NotAcceptableValueException;
use App\Service\Utility\DomainHelper;
use App\User\Entity\Interfaces\AppUserInterface as UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use App\User\Repository\UserRepository;
use App\Security\Domain\AuthUserInterface;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Factory\UlidFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this name')]
#[UniqueEntity(fields: ['google_id'], message: 'There is already an account with this google id')]
#[ORM\Index(columns: ['avatar_status'], name: "avatar_status_filterx")]
#[ORM\Index(columns: ['is_verified'], name: "is_verified_idx")]
#[ORM\Index(columns: ['is_approved'], name: "is_approved_idx")]
#[ORM\Index(columns: ['is_paying'], name: "is_paying_idx")]
class User implements AuthUserInterface, LegacyPasswordAuthenticatedUserInterface, UserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_AUTH_TYPE_CMS = 'auth_type_cms';

    public const STATUS_ACTIVE       = 'active';
    public const STATUS_NEED_APPROVE = 'need_approve';
    public const STATUS_BLOCKED      = 'blocked';
    public const STATUS_DELETED      = 'deleted';

    public const APPROVE_STATUS_NEED_APPROVE        = 'need_approve';
    public const APPROVE_STATUS_WAITING_FOR_APPROVE = 'waiting_for_approve';
    public const APPROVE_STATUS_NOT_APPROVED        = 'not_approved';
    public const APPROVE_STATUS_APPROVED            = 'approved';

    public const LABEL_IS_APPROVED = 'blue';
    public const LABEL_IS_VERIFIED = 'white';
    public const LABEL_DEFAULT     = null;

    public const AVATAR_STATUS_NEW             = 'new';
    public const AVATAR_STATUS_WAITING_APPROVE = 'waiting_approve';
    public const AVATAR_STATUS_ACTIVE          = 'active';
    public const AVATAR_STATUS_BLOCKED         = 'blocked';

    public const SOURCE_GOOGLE   = 'google';
    public const SOURCE_FACEBOOK = 'facebook';
    public const SOURCE_INSTAGRAM = 'instagram';
    public const SOURCE_TWITTER = 'twitter';
    public const SOURCE_REDDIT = 'reddit';
    public const SOURCE_YOUTUBE = 'youtube';
    public const SOURCE_TWITCH = 'twitch';
    public const SOURCE_TIKTOK = 'tiktok';
    public const SOURCE_X_COM = 'x.com';

    public const AVAILABLE_SOURCES = [
        self::SOURCE_GOOGLE,
        self::SOURCE_FACEBOOK,
        self::SOURCE_INSTAGRAM,
        self::SOURCE_TWITTER,
        self::SOURCE_REDDIT,
        self::SOURCE_YOUTUBE,
        self::SOURCE_TWITCH,
        self::SOURCE_TIKTOK,
        self::SOURCE_X_COM,
    ];

    public const AVAILABLE_AVATAR_STATUSES = [
        self::AVATAR_STATUS_NEW,
        self::AVATAR_STATUS_WAITING_APPROVE,
        self::AVATAR_STATUS_ACTIVE,
        self::AVATAR_STATUS_BLOCKED,
    ];

    public const PAYING_CAT_0 = 0;
    public const PAYING_CAT_1 = 1;
    public const PAYING_CAT_2 = 2;
    public const PAYING_CAT_3 = 3;

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

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $bioLink;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $facebookId = null;

    #[ORM\Column(nullable: true)]
    private ?string $approveStatus = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false, options: ['default' => 0])]
    private float $balance = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false, options: ['default' => 0])]
    private float $balanceRu = 0;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserSocials::class, cascade: ['persist', 'remove'],)]
    private UserSocials|null $socials = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserInfo::class, cascade: ['persist', 'remove'])]
    private UserInfo|null $info = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserAvatar::class, cascade: ['persist', 'remove'])]
    private UserAvatar|null $avatarData = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserImageProfile::class, cascade: ['persist', 'remove'])]
    private UserImageProfile|null $imageProfileData = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserPhotoProfile::class, cascade: ['persist', 'remove'])]
    private UserPhotoProfile|null $photoProfileData = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserAudioProfile::class, cascade: ['persist', 'remove'])]
    private UserAudioProfile|null $audioProfileData = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserBlock::class, cascade: ['persist', 'remove'])]
    private UserBlock|null $block = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserDecline::class, cascade: ['persist', 'remove'])]
    private UserDecline|null $decline = null;

    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $ulid;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isApproved = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isBlocked = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isAnonym = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPaying = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isTalker = false;

    #[ORM\Column(nullable: true)]
    private ?string $anonymUserId = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: FirebaseToken::class, cascade: ['persist', 'remove'])]
    private FirebaseToken|null $firebase_token = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastPaymentAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastActivityAt = null;

    #[ORM\OneToMany(mappedBy: "follower", targetEntity: Follow::class)]
    private Collection $following;

    #[ORM\OneToMany(mappedBy: "following", targetEntity: Follow::class)]
    private Collection $followers;

    #[ORM\OneToMany(mappedBy: "author", targetEntity: View::class)]
    private Collection $viewers;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPWA = false;

    #[ManyToOne(targetEntity: Team::class, inversedBy: "users")]
    #[JoinColumn(name: "team_id", referencedColumnName: "id", onDelete: "SET NULL")]
    private ?Team $team;

    public function __construct()
    {
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->viewers = new ArrayCollection();

        $this->createdAt = new \DateTimeImmutable('now');

        $this->avatarStatus = self::AVATAR_STATUS_NEW;

        if (!$this->socials) {
            $this->socials = new UserSocials();
            $this->socials->setUser($this);
        }

        if (!$this->info) {
            $this->info = new UserInfo();
            $this->info->setUser($this);
        }

        if (!$this->avatarData) {
            $this->avatarData = new UserAvatar();
            $this->avatarData->setUser($this);
        }

        if (!$this->imageProfileData) {
            $this->imageProfileData = new UserImageProfile();
            $this->imageProfileData->setUser($this);
        }

        if (!$this->photoProfileData) {
            $this->photoProfileData = new UserPhotoProfile();
            $this->photoProfileData->setUser($this);
        }

        if (!$this->audioProfileData) {
            $this->audioProfileData = new UserAudioProfile();
            $this->audioProfileData->setUser($this);
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

    public function getLabel(): ?string
    {
        if ($this->isApproved()) {
            return self::LABEL_IS_APPROVED;
        }

        if ($this->isVerified) {
            return self::LABEL_IS_VERIFIED;
        }

        return self::LABEL_DEFAULT;
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

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): self
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function isOnApprove(): bool
    {
        return $this->approveStatus === self::APPROVE_STATUS_WAITING_FOR_APPROVE;
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

    public function getBioLink(): ?string
    {
        return $this->bioLink;
    }

    public function setBioLink(?string $bioLink): self
    {
        $this->bioLink = $bioLink;

        return $this;
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

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): self
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getInstagramUsername(): ?string
    {
        return $this->getSocials()->getInstagram();
    }

    public function setInstagramUsername(?string $instagramUsername): self
    {
        $this->getSocials()->setInstagram($instagramUsername);

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

    public function getCropAvatarUrl(): ?string
    {
        return $this->getAvatarData()->getCropUrl();
    }

    public function getImageProfileCropUrl(): ?string
    {
        if ($crop = $this->getImageProfileData()->getCropUrl()) {
            return $crop;
        }

        return $this->imageProfileCover();
    }

    public function getPhotoProfileCropUrl(): ?string
    {
        if ($crop = $this->getPhotoProfileData()->getCropUrl()) {
            return $crop;
        }

        return $this->getPhotoProfileData()->getOriginalUrl();
    }

    public function getAudioProfileUrl(): ?string
    {
        return $this->getAudioProfileData()->getUrl();
    }

    public function getPublicAudioProfileUrl(): ?string
    {
        $audioData = $this->getAudioProfileData();

        return $audioData->getUrl() && $audioData->isApproved() ? $audioData->getUrl() : null;
    }

    public function getImageProfileUrl(): ?string
    {
        $imageProfile = $this->getImageProfileData();
        $image = $imageProfile->isApproved() ? $imageProfile->getCrop() : $imageProfile->getCropBlur();
        if (!$image) {
            return $this->imageProfileCover();
        }

        return sprintf('%s/%s', DomainHelper::getCdnDomain(), $image);
    }

    public function getPhotoProfileUrl(): ?string
    {
        $photoProfile = $this->getPhotoProfileData();

        if ($photoProfile->getCrop()) {
            return sprintf('%s/%s', DomainHelper::getCdnDomain(), $photoProfile->getCrop());
        }

        return $photoProfile->getOriginalUrl();
    }

    public function imageProfileCover(): string
    {
        return self::getImageProfileCover($this->id);
    }

    public static function getImageProfileCover(int $id): string
    {
        return sprintf('%s/profile-image-covers/%s.png', DomainHelper::getCdnDomain(), $id % 5);
    }

    public function hasImageProfile(): bool
    {
        $imageProfile = $this->getImageProfileData();
        $image = $imageProfile->isApproved() ? $imageProfile->getCrop() : $imageProfile->getCropBlur();
        if (!$image) {
            return false;
        }

        return true;
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

    public function getTotalSum(): float
    {
        return $this->balance;
    }

    public function setTotalSum(float $sum): self
    {
        $this->balance = $sum;

        return $this;
    }

    public function addSum(float|int $sum): self
    {
        $this->balance += (float)$sum;

        return $this;
    }

    public function getTotalSumRu(): float
    {
        return $this->balanceRu;
    }

    public function setTotalSumRu(float $sum): void
    {
        $this->balanceRu = $sum;
    }

    public function addSumRu(float|int $sumRu): self
    {
        $this->balanceRu += (float)$sumRu;

        return $this;
    }

    public function getSalt(): ?string
    {
        return md5('passSalt'.$this->email);
    }

    public function getSocials(): UserSocials
    {
        if (!$this->socials) {
            $this->socials = new UserSocials();
            $this->socials->setUser($this);
        }

        return $this->socials;
    }

    public function setSocials(UserSocials $socials): self
    {
        $this->socials = $socials;

        return $this;
    }

    public function getInfo(): ?UserInfo
    {
        if (!$this->info) {
            $this->info = new UserInfo();
            $this->info->setUser($this);
        }

        return $this->info;
    }

    public function setCountry(?string $country): self
    {
        $this->getInfo()->setCountry($country);

        return $this;
    }

    public function setInfo(?UserInfo $info): void
    {
        $this->info = $info;
    }

    public function setRegPoint(?string $regPoint): self
    {
        $this->getInfo()->setRegPoint($regPoint);

        return $this;
    }

    public function getRegPoint(): string
    {
        return $this->getInfo()->getRegPoint();
    }

    public function setRegSource(?string $regSource): self
    {
        $this->getInfo()->setRegSource($regSource);

        return $this;
    }

    public function getRegSource(): string
    {
        return $this->getInfo()->getRegSource();
    }

    public function setRegFromUserUid(?string $userUid): self
    {
        $this->getInfo()->setRegFromUserUid($userUid);

        return $this;
    }

    public function getRegFromUserUid(): ?string
    {
        return $this->getInfo()->getRegFromUserUid();
    }

    public function setInstagramLink(?string $instagramLink): self
    {
        $this->getSocials()->setInstagramLink($instagramLink);

        return $this;
    }

    public function getInstagramLink(): ?string
    {
        return $this->getSocials()->getInstagramLink();
    }

    public function setFacebookUsername(?string $username): self
    {
        $this->getSocials()->setFacebook($username);

        return $this;
    }

    public function getFacebookUsername(): ?string
    {
        return $this->getSocials()->getFacebook();
    }

    public function setFacebookLink(?string $link): self
    {
        $this->getSocials()->setFacebookLink($link);

        return $this;
    }

    public function getFacebookLink(): ?string
    {
        return $this->getSocials()->getFacebookLink();
    }

    public function setTwitterUsername(?string $username): self
    {
        $this->getSocials()->setTwitter($username);

        return $this;
    }

    public function getTwitterUsername(): ?string
    {
        return $this->getSocials()->getTwitter();
    }

    public function setTwitterLink(?string $link): self
    {
        $this->getSocials()->setTwitterLink($link);

        return $this;
    }

    public function getTwitterLink(): ?string
    {
        return $this->getSocials()->getTwitterLink();
    }

    public function setRedditUsername(?string $username): self
    {
        $this->getSocials()->setReddit($username);

        return $this;
    }

    public function getRedditUsername(): ?string
    {
        return $this->getSocials()->getReddit();
    }

    public function setRedditLink(?string $link): self
    {
        $this->getSocials()->setRedditLink($link);

        return $this;
    }

    public function getRedditLink(): ?string
    {
        return $this->getSocials()->getRedditLink();
    }

    public function setXComUsername(?string $username): self
    {
        $this->getSocials()->setXCom($username);

        return $this;
    }

    public function getXComUsername(): ?string
    {
        return $this->getSocials()->getXCom();
    }

    public function setXComLink(?string $link): self
    {
        $this->getSocials()->setXComLink($link);

        return $this;
    }

    public function getXComLink(): ?string
    {
        return $this->getSocials()->getXComLink();
    }

    public function setYoutubeUsername(?string $username): self
    {
        $this->getSocials()->setYoutube($username);

        return $this;
    }

    public function getYoutubeUsername(): ?string
    {
        return $this->getSocials()->getYoutube();
    }

    public function setYoutubeLink(?string $link): self
    {
        $this->getSocials()->setYoutubeLink($link);

        return $this;
    }

    public function getYoutubeLink(): ?string
    {
        return $this->getSocials()->getYoutubeLink();
    }

    public function setTwitchUsername(?string $username): self
    {
        $this->getSocials()->setTwitch($username);

        return $this;
    }

    public function getTwitchUsername(): ?string
    {
        return $this->getSocials()->getTwitch();
    }

    public function setTwitchLink(?string $link): self
    {
        $this->getSocials()->setTwitchLink($link);

        return $this;
    }

    public function getTwitchLink(): ?string
    {
        return $this->getSocials()->getTwitchLink();
    }

    public function setTiktokUsername(?string $username): self
    {
        $this->getSocials()->setTiktok($username);

        return $this;
    }

    public function getTiktokUsername(): ?string
    {
        return $this->getSocials()->getTiktok();
    }

    public function setTiktokLink(?string $link): self
    {
        $this->getSocials()->setTiktokLink($link);

        return $this;
    }

    public function getTiktokLink(): ?string
    {
        return $this->getSocials()->getTiktokLink();
    }

    public function getAvatarData(): ?UserAvatar
    {
        if (!$this->avatarData) {
            $this->avatarData = new UserAvatar();
            $this->avatarData->setUser($this);
        }

        return $this->avatarData;
    }

    public function getImageProfileData(): UserImageProfile
    {
        if (!$this->imageProfileData) {
            $this->imageProfileData = new UserImageProfile();
            $this->imageProfileData->setUser($this);
        }

        return $this->imageProfileData;
    }

    public function getPhotoProfileData(): UserPhotoProfile
    {
        if (!$this->photoProfileData) {
            $this->photoProfileData = new UserPhotoProfile();
            $this->photoProfileData->setUser($this);
        }

        return $this->photoProfileData;
    }

    public function getAudioProfileData(): UserAudioProfile
    {
        if (!$this->audioProfileData) {
            $this->audioProfileData = new UserAudioProfile();
            $this->audioProfileData->setUser($this);
        }

        return $this->audioProfileData;
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

    public function getApproveStatus(): ?string
    {
        if (!$this->approveStatus) {
            return $this->isApproved() ? self::APPROVE_STATUS_APPROVED : self::APPROVE_STATUS_NEED_APPROVE;
        }

        return $this->approveStatus;
    }

    public function setApproveStatus(?string $approveStatus): void
    {
        $this->approveStatus = $approveStatus;
    }

    public function getStatus(bool $forCurrentUser = false): string
    {
        if ($this->isDeleted) {
            return self::STATUS_DELETED;
        }

        if ($this->isBlocked) {
            return self::STATUS_BLOCKED;
        }

        if ($forCurrentUser && !$this->isApproved()) {
            return $this->getApproveStatus();
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

    public function isAiChat(): bool
    {
        return (bool)($this->getInfo()?->isAiChat());
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

    public function isUnsubscribed(): bool
    {
        return (bool)($this->getInfo()?->isUnsubscribed());
    }

    public function getCountry(): ?string
    {
        return $this->getInfo()?->getCountry();
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

    public function getSlogan(): ?string
    {
        return $this->getInfo()?->getSlogan();
    }

    public function getDescription(): ?string
    {
        return $this->getInfo()?->getDescription();
    }

    public function setSlogan(?string $slogan): self
    {
        $this->getInfo()->setSlogan($slogan);

        return $this;
    }

    public function getFirebaseToken(): ?FirebaseToken
    {
        return $this->firebase_token;
    }

    public function setFirebaseToken(?FirebaseToken $firebase_token): self
    {
        $this->firebase_token = $firebase_token;

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

    public function getLastPaymentAtFormat(string $format = 'Y-m-d H:i:s'): ?string
    {
        return $this->lastPaymentAt?->format($format);
    }

    public function getPaymentsTotal(): ?float
    {
        return $this->getTotalSum();
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isAnonym(): bool
    {
        return $this->isAnonym;
    }

    public function setIsAnonym(bool $isAnonym): self
    {
        $this->isAnonym = $isAnonym;

        return $this;
    }

    public function getAnonymUserId(): ?string
    {
        return $this->anonymUserId;
    }

    public function setAnonymUserId(?string $anonymUserId): self
    {
        $this->anonymUserId = $anonymUserId;

        return $this;
    }

    public function isPaying(): bool
    {
        return $this->isPaying;
    }

    public function setIsPaying(bool $isPaying): self
    {
        $this->isPaying = $isPaying;
        $this->isTalker = false;

        return $this;
    }

    public function isTalker(): bool
    {
        return $this->isTalker;
    }

    public function setIsTalker(bool $isTalker): self
    {
        $this->isTalker = $isTalker;

        return $this;
    }

    public function getFollowersCount(): int
    {
        return $this->followers->count();
    }

    public function getFollowingCount(): int
    {
        return $this->following->count();
    }

    public function getViewersCount(): int
    {
        return $this->viewers->count();
    }

    public function isPWA(): bool
    {
        return $this->isPWA;
    }

    public function setIsPWA(bool $isPWA): self
    {
        $this->isPWA = $isPWA;

        return $this;
    }

    public function getUidStr(): string
    {
        return (string)$this->ulid;
    }

    public function getUserUrl(): string
    {
        return sprintf('%s/profile/%s', $_ENV['WEB_PROJECT_DOMAIN'], $this->getUidStr());
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    private function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getUserType(): string
    {
        return $this->isAnonym() ? 'anonym' : 'user';
    }

    public function isInstagram(): bool
    {
        return $this->getSource() === self::SOURCE_INSTAGRAM && $this->getInstagramLink();
    }

    public function isSocialAnonym(): bool
    {
        return $this->isAnonym() && $this->getSource() && !$this->isPaying();
    }

    public function getSourceSocial(): ?string
    {
        if ($this->isApproved()) {
            return null;
        }

        $social = $this->getSocials();

        if ($social->getInstagram()) {
            return self::SOURCE_INSTAGRAM;
        }

        if ($social->getFacebook()) {
            return self::SOURCE_FACEBOOK;
        }

        if ($social->getTwitter()) {
            return self::SOURCE_TWITTER;
        }

        if ($social->getReddit()) {
            return self::SOURCE_REDDIT;
        }

        if ($social->getXCom()) {
            return self::SOURCE_X_COM;
        }

        if ($social->getTiktok()) {
            return self::SOURCE_TIKTOK;
        }

        if ($social->getYoutube()) {
            return self::SOURCE_YOUTUBE;
        }

        if ($social->getTwitch()) {
            return self::SOURCE_TWITCH;
        }

        return null;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastPaymentAt(): ?\DateTimeImmutable
    {
        return $this->lastPaymentAt;
    }

    /**
     * @param \DateTimeImmutable|null $lastPaymentAt
     */
    public function setLastPaymentAt(?\DateTimeImmutable $lastPaymentAt): void
    {
        $this->lastPaymentAt = $lastPaymentAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastActivityAt(): ?\DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    /**
     * @param \DateTimeImmutable|null $lastActivityAt
     */
    public function setLastActivityAt(?\DateTimeImmutable $lastActivityAt): void
    {
        $this->lastActivityAt = $lastActivityAt;
    }

    public function isAdmin(): bool
    {
        return $this->getInfo()->isAdmin();
    }

    public function isAI(): bool
    {
        return $this->getInfo()->isAI();
    }


    public function getPayingCategory(): ?int
    {
        if ($this->isApproved()) {
            return 0;
        }

        return $this->getInfo()->getPayingCategory();
    }

    public function setPayingCategory(int $category): self
    {
        $this->getInfo()->setPayingCategory($category);

        return $this;
    }

    public function setDeviceID(string $deviceID): self
    {
        $this->getInfo()->setDeviceID($deviceID);

        return $this;
    }

    public function getDeviceID(): ?string
    {
        return $this->getInfo()->getDeviceID();
    }

    public function isFan(): bool
    {
        return !$this->isApproved();
    }

    public function isModel(): bool
    {
        return $this->isApproved();
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function hasChatAutoMessaging(): bool
    {
        return $this->getInfo()->hasChatAutoMessaging();
    }

    public function setMirrorUserId(string $mirrorUserId): self
    {
        $this->getInfo()->setMirrorUserId($mirrorUserId);

        return $this;
    }

    public function getMirrorUserId(): ?string
    {
        return $this->getInfo()->getMirrorUserId();
    }
}
