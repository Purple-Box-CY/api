<?php

namespace App\User\DataProvider;

use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\Utility\FormatHelper;
use App\Service\Utility\MomentHelper;
use App\User\Entity\Cache\UserCache;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Symfony\Component\Uid\Ulid;

class UserDataProvider
{
    public function __construct(
        private UserRepository        $userRepository,
        private RedisService          $redisService,
    ) {
    }

    public function getUserByEmail(string $email): ?User
    {
        $email = mb_strtolower($email);

        return $this->userRepository->findOneBy(['email' => $email]);
    }

    public function getUserByUid(?string $uid, bool $fromCache = true): User|UserCache|null
    {
        if (!$uid) {
            return null;
        }

        if (!FormatHelper::isValidUid($uid)) {
            return null;
        }

        if ($fromCache) {
            return $this->getUserByUidFromCache($uid);
        }

        return $this->userRepository->findOneBy(['ulid' => new Ulid($uid)]);
    }

    public function getUserById(int $userId, bool $fromCache = true): User|UserCache|null
    {
        return $this->getUserByIdFromCache($userId, $fromCache);
    }

    /**
     * @param array $ids
     *
     * @return User[]
     */
    public function getUsersByIds(array $ids): array
    {
        return $this->userRepository->findBy(['id' => $ids]);
    }

    public function getUserByUsername(string $username, bool $fromCache = true): User|UserCache|null
    {
        $username = mb_strtolower($username);
        if ($fromCache) {
            return $this->getUserByUsernameFromCache($username);
        }

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            return null;
        }

        if ($user->getMirrorUserId()) {
            return $this->getUserById($user->getMirrorUserId());
        }

        return $user;
    }

    public function getUserByGoogleId(string $googleId): ?User
    {
        return $this->userRepository->findOneBy(['googleId' => $googleId]);
    }

    public function getUserByFacebookId(string $facebookId): ?User
    {
        return $this->userRepository->findOneBy(['facebookId' => $facebookId]);
    }

    public function saveUser(User $user, $invalidateCache = true): User
    {
        $user = $this->userRepository->save($user);
        if ($invalidateCache) {
            $this->redisService->invalidateCacheByUser($user);
        }

        return $user;
    }

    public function getUserByUidFromCache(string $uid): ?UserCache
    {
        $this->redisService->setPrefix(RedisKeys::PREFIX_USER);
        $key = sprintf(RedisKeys::KEY_USER_ITEM, $uid);

        /** @var UserCache $userCache */
        $userCache = $this->redisService->getObject($key);
        if ($userCache) {
            return UserCache::createFromCache($userCache);
        }

        try {
            $user = $this->userRepository->findOneBy(['ulid' => new Ulid($uid)]);
        } catch (\Exception $e) {
            $user = null;
        }

        if (!$user) {
            return null;
        }

        $userCache = UserCache::create($user);
        $keyId = sprintf(RedisKeys::KEY_USER_ITEM, $user->getId());

        $this->redisService->setObject($key, $userCache, MomentHelper::SECONDS_WEEK);
        $this->redisService->setObject($keyId, $userCache, MomentHelper::SECONDS_WEEK);

        return $userCache;
    }

    public function getUserByIdFromCache(int $id, bool $fromCache = true): User|UserCache|null
    {
        $this->redisService->setPrefix(RedisKeys::PREFIX_USER);
        $keyId = sprintf(RedisKeys::KEY_USER_ITEM, $id);

        /** @var UserCache $userCache */
        $userCache = $this->redisService->getObject($keyId);
        if ($userCache && $fromCache) {
            return UserCache::createFromCache($userCache);
        }

        try {
            $user = $this->userRepository->findOneBy(['id' => $id]);
        } catch (\Exception $e) {
            $user = null;
        }

        if (!$user) {
            return null;
        }

        $userCache = UserCache::create($user);
        $keyUid = sprintf(RedisKeys::KEY_USER_ITEM, $user->getUidStr());

        $this->redisService->setObject($keyId, $userCache, MomentHelper::SECONDS_WEEK);
        $this->redisService->setObject($keyUid, $userCache, MomentHelper::SECONDS_WEEK);

        return $fromCache ? $userCache : $user;
    }

    public function getUserByUsernameFromCache(string $username): User|UserCache|null
    {
        $this->redisService->setPrefix(RedisKeys::PREFIX_USER);
        $key = sprintf(RedisKeys::KEY_USER_ITEM, $username);

        /** @var UserCache $userCache */
        $userCache = $this->redisService->getObject($key);
        if ($userCache) {
            return UserCache::createFromCache($userCache);
        }

        try {
            $user = $this->userRepository->findOneBy(['username' => $username]);
        } catch (\Exception $e) {
            $user = null;
        }

        if (!$user) {
            return null;
        }

        if ($user->getMirrorUserId()) {
            return $this->getUserById($user->getMirrorUserId());
        }

        $userCache = UserCache::create($user);
        $this->redisService->setObject($key, $userCache, MomentHelper::SECONDS_WEEK);

        return $userCache;
    }

}
