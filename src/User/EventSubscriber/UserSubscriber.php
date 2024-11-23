<?php

declare(strict_types=1);

namespace App\User\EventSubscriber;

use App\Service\Infrastructure\RedisService;
use App\User\Event\DeleteUserEvent;
use App\User\Event\Interfaces\UserEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RedisService        $redisService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeleteUserEvent::NAME                => [
                ['invalidateUserCache'],
            ],
        ];
    }
    public function invalidateUserCache(UserEventInterface $event): void
    {
        $this->redisService->invalidateCacheByUser($event->getUser());
    }

}
