<?php

namespace App\User\Service;

use App\Exception\Http\AccessDenied\ObjectIsBlockedException;
use App\Exception\Http\AccessDenied\UserIsDeletedException;
use App\Exception\Http\AccessDenied\UserIsNeedReloginException;
use App\Exception\Http\AccessDenied\UserIsNotApprovedException;
use App\Exception\Http\NotFound\UserNotFoundHttpException;
use App\Security\Domain\UserFetcherInterface;
use App\Service\Infrastructure\ImageService;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\S3Service;
use App\Service\Utility\ImageHelper;
use App\User\DataProvider\UserDataProvider;
use App\User\Domain\UserFactory;
use App\User\Entity\Cache\UserCache;
use App\User\Entity\Interfaces\AppUserInterface as UserInterface;
use App\User\Entity\User;
use App\User\Event\DeleteUserEvent;
use App\User\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserService
{
    public const AVATAR_DIR              = 'uploads/user_avatar';
    public const AVATAR_CROP_WIDTH       = 100;
    public const AVATAR_CROP_HEIGHT      = 100;

    public function __construct(
        private string                   $publicDir,
        private ImageService             $imageService,
        private S3Service                $s3Service,
        private EventDispatcherInterface $dispatcher,
        private UserFactory              $userFactory,
        private UserRepository           $userRepository,
        private UserFetcherInterface     $userFetcher,
        private UserDataProvider         $userDataProvider,
        private UserEventService         $userEventService,
        private RedisService             $redisService,
        private LogService               $logger,
    ) {
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->userDataProvider->getUserByEmail($email);
    }

    public function getUserByUid(?string $uid, bool $fromCache = true): User|UserCache|null
    {
        return $this->userDataProvider->getUserByUid($uid, $fromCache);
    }

    public function getUserByIdFromCache(int $userId): User|UserCache|null
    {
        return $this->userDataProvider->getUserById($userId, true);
    }

    public function getUserById(int $userId, bool $fromCache = false): User|UserCache|null
    {
        return $this->userDataProvider->getUserById($userId, $fromCache);
    }

    /**
     * @param array $ids
     *
     * @return User[]
     */
    public function getUsersByIds(array $ids): array
    {
        return $this->userDataProvider->getUsersByIds($ids);
    }

    public function getUserByUsername(string $username, bool $fromCache = true): User|UserCache|null
    {
        return $this->userDataProvider->getUserByUsername($username, $fromCache);
    }

    public function getUserByGoogleId(string $googleId): ?User
    {
        return $this->userDataProvider->getUserByGoogleId($googleId);
    }

    public function getUserByFacebookId(string $facebookId): ?User
    {
        return $this->userDataProvider->getUserByFacebookId($facebookId);
    }

    public function changePassword(User $user, string $password): User
    {
        $this->userFactory->changePassword($user, $password);

        return $user;
    }

    public function createUser(
        string  $email,
        string  $name,
        string  $password,
        ?string $regPoint = null,
        ?string $fromUserUid = null,
        ?string $regSource = null,
    ): User {
        $user = $this->userFactory->create(
            email: $email,
            username: $name,
            password: $password,
        );
        if ($regPoint) {
            $user->setRegPoint($regPoint);
        }
        if ($regSource) {
            $user->setRegSource($regSource);
        }
        if ($fromUserUid) {
            $user->setRegFromUserUid($fromUserUid);
        }

        $user = $this->userRepository->save($user);

        $this->userEventService->sendEventCreateUser($user);

        return $user;
    }

    public function saveUser(User $user): User
    {
        return $this->userDataProvider->saveUser($user);
    }

    public function getCurrentUser(): ?UserInterface
    {
        try {
            $user = $this->userFetcher->getCurrentUser();
        } catch (\Throwable $e) {
            $this->logger->error('Failed to get auth user',
                [
                    'method' => __METHOD__,
                    'error'  => $e->getMessage(),
                ]);
            $user = null;
        }

        return $user;
    }

    public function checkUser(
        ?UserInterface $user,
        bool           $isCurrentUser = true,
        bool           $availableForBlocked = false,
        bool           $needApprove = false,
        bool           $checkNeedRelogin = true,
    ): bool {
        if (!$user) {
            if ($isCurrentUser) {
                throw new UnauthorizedHttpException('User is not authorized');
            } else {
                throw new UserNotFoundHttpException('User is not found');
            }
        }

        if ($user->isBlocked() && !$availableForBlocked) {
            throw new ObjectIsBlockedException(sprintf('User %s is blocked', $user->getUid()));
        }

        if ($user->isDeleted()) {
            throw new UserIsDeletedException(sprintf('User %s is deleted', $user->getUid()));
        }

        return true;
    }

    public function uploadUserAvatar(User $user, File $file): User
    {
        $userAvatarDir = UserService::AVATAR_DIR;
        $userAvatarDirFullPath = sprintf('%s/%s', $this->publicDir, $userAvatarDir);
        if (!file_exists($userAvatarDirFullPath)) {
            mkdir($userAvatarDirFullPath, 0777, true);
        }

        $tmpFile = '/tmp/'.ImageHelper::generateFileName($file->guessExtension());
        file_put_contents($tmpFile, $file->getContent());

        $rawJpg = ImageHelper::convertImageToJpg($tmpFile);
        $jpgPublicPath = sprintf('%s/%s', $userAvatarDir, ImageHelper::generateFileName('jpg'));
        $jpgPublicPathFull = sprintf('%s/%s', $this->publicDir, $jpgPublicPath);

        $cropPublicPath = sprintf('%s/%s', $userAvatarDir, ImageHelper::generateFileName('jpg'));
        $cropPublicPathFull = sprintf('%s/%s', $this->publicDir, $cropPublicPath);

        $blurPublicPath = sprintf('%s/%s', $userAvatarDir, ImageHelper::generateFileName('jpg'));
        $blurPublicPathFull = sprintf('%s/%s', $this->publicDir, $blurPublicPath);

        file_put_contents($jpgPublicPathFull, $rawJpg);
        ImageHelper::correctImageOrientation($jpgPublicPathFull);

        $size = getimagesize($jpgPublicPathFull);
        $width = $size[0] ?? null;
        $height = $size[1] ?? null;

        if ($width < self::AVATAR_CROP_WIDTH || $height < self::AVATAR_CROP_HEIGHT) {
            $error = sprintf('Image size is smaller than required %sx%s',
                self::AVATAR_CROP_WIDTH,
                self::AVATAR_CROP_HEIGHT);
            $this->logger->debug($error,
                [
                    'jpgPublicPath'     => $jpgPublicPath,
                    'jpgPublicPathFull' => $jpgPublicPathFull,
                    'width'             => $width,
                    'height'            => $height,
                ]);
//            unlink($tmpFile);
//            unlink($jpgPublicPathFull);
            throw new BadRequestException($error);
        }

        $this->imageService->cropImage($jpgPublicPath, $cropPublicPathFull, ImageService::THUMB_AVATAR);
        $this->imageService->createBlur($cropPublicPathFull, $blurPublicPathFull);

        $cdnJpg = $this->s3Service->uploadContentToCDN(ImageHelper::fileGetContents($jpgPublicPathFull),
            $jpgPublicPath,
            true);
        $cdnCrop = $this->s3Service->uploadContentToCDN(ImageHelper::fileGetContents($cropPublicPathFull),
            $cropPublicPath,
            true);
        $cdnBlur = $this->s3Service->uploadContentToCDN(ImageHelper::fileGetContents($blurPublicPathFull),
            $blurPublicPath,
            true);

        unlink($tmpFile);
        unlink($jpgPublicPathFull);
        unlink($cropPublicPathFull);
        unlink($blurPublicPathFull);

        $user
            ->setAvatar($blurPublicPath)
            ->setAvatarStatus(User::AVATAR_STATUS_WAITING_APPROVE);
        $user->getAvatarData()
            ->setOriginal($jpgPublicPath)
            ->setCrop($cropPublicPath)
            ->setCropBlur($blurPublicPath);

        $user = $this->saveUser($user);

        $this->userEventService->sendEventUserUpdateAvatar($user);

        return $user;
    }

    public function deleteUser(User $user): User
    {
        $user->setIsDeleted(true);

        $user = $this->saveUser($user);

        $this->dispatcher->dispatch(new DeleteUserEvent($user), DeleteUserEvent::NAME);

        return $user;
    }


    public function unsubscribeFromNotifications(User $user): bool
    {
        try {
            $user->getInfo()->setUnsubscribed(true);
            $this->saveUser($user);
        } catch (\Exception $e) {
            $this->logger->error('Failed to unsubscribe user',
                [
                    'user_uid' => $user->getUid(),
                    'error'    => $e->getMessage(),
                ]);

            return false;
        }

        return true;
    }

    public function renameDeletedUser(User $user): void
    {
        $user->setEmail('deleted_'.$user->getId().'_'.$user->getEmail())
            ->setUsername('deleted_'.$user->getId().'_'.$user->getUsername());

        $this->userDataProvider->saveUser($user);
    }

    public function updateUserLastActivity(int $userId, ?\DateTimeImmutable $dateTime = null): void
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return;
        }

        if (!$dateTime) {
            $dateTime = new \DateTimeImmutable('now');
        }
        $user->setLastActivityAt($dateTime);
        $this->userRepository->save($user);
    }

    public function findUser(string $id): User|UserCache|null
    {
        try {
            $user = $this->getUserByUid($id);
            if (!$user) {
                $user = $this->getUserByUsername($id);
            }
        } catch (\Exception $e) {
            $this->logger->error('Runtime error when get user by uid',
                [
                    'error'  => $e->getMessage(),
                    'method' => __METHOD__,
                ]);
            $user = null;
        }

        return $user;
    }

}
