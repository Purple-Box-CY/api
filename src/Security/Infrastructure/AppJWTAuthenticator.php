<?php

namespace App\Security\Infrastructure;

use App\Security\DTO\JWTAppUser;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\Utility\MomentHelper;
use App\User\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppJWTAuthenticator extends JWTAuthenticator
{
    private RedisService $redisService;
    private LogService $logger;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        UserProviderInterface    $userProvider,
        TokenExtractorInterface  $tokenExtractor,
        TranslatorInterface      $translator,
        RedisService             $redisService,
        LogService               $logService,
    ) {
        $this->logger = $logService;
        $this->redisService = $redisService;
        $this->redisService->setPrefix(RedisKeys::PREFIX_JWT);

        parent::__construct($jwtManager,
            $eventDispatcher,
            $tokenExtractor,
            $userProvider,
            $translator,
        );
    }

    protected function loadUser(array $payload, string $identity): UserInterface
    {
        $roles = $payload['roles'] ?? [];
        try {
            $user = $this->getUserFromCache($identity, $roles);
            if ($user) {
                return $user;
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user by jwt',
                [
                    'error'    => $e->getMessage(),
                    'jwt_user' => $identity,
                ]);
        }

        /** @var User $user */
        $user = parent::loadUser($payload, $identity);
        if ($user) {
            try {
                $this->saveUserToCache($user, $identity);
            } catch (\Exception $e) {
                $this->logger->error('Failed to save user by jwt',
                    [
                        'error'    => $e->getMessage(),
                        'jwt_user' => $identity,
                        'user_uid' => $user->getUid(),
                    ]);
            }
            if ($roles) {
                $user->setRoles($roles);
            }
        }

        return $user;
    }

    private function getUserFromCache(string $identity, ?array $roles = []): ?UserInterface
    {
        $this->redisService->setPrefix(RedisKeys::PREFIX_JWT);
        $key = sprintf(RedisKeys::KEY_JWT_ITEM, $identity);

        /** @var JWTAppUser $userCache */
        $userCache = $this->redisService->getObject($key);

        if (!$userCache) {
            return null;
        }

        $user = JWTAppUser::createFromCache($userCache);
        if ($roles) {
            $user->setRoles($roles);
        }

        return $user;
    }

    private function saveUserToCache(User $user, string $identity): void
    {
        $this->redisService->setPrefix(RedisKeys::PREFIX_JWT);
        $key = sprintf(RedisKeys::KEY_JWT_ITEM, $identity);

        $userCache = JWTAppUser::create($user);
        $this->redisService->setObject($key, $userCache, MomentHelper::SECONDS_WEEK);
    }

    public function getUserByToken(array $payload, string $identity): ?UserInterface
    {
        return $this->loadUser($payload, $identity);
    }
}