<?php

namespace App\EventListener;

use App\ApiDTO\Response\Auth\TokenResponse;
use App\Security\Domain\AuthUserInterface;
use App\Service\Infrastructure\RedisService;
use App\User\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    public function __construct(
        private readonly RedisService        $redisService,
        private readonly UserService         $userService,
    ) {
    }

    /**
     * @param AuthenticationSuccessEvent $event
     *
     * @return void
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        /** @var AuthUserInterface $user */
        $user = $event->getUser();
        $data[TokenResponse::FIELD_CURRENT_USER_UID] = (string)$user->getUid();

        $event->setData($data);

        $this->redisService->invalidateCacheByUser($user);
        $this->userService->updateUserLastActivity($user->getId());
    }
}
