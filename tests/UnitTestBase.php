<?php

namespace App\Tests;

use App\Security\Infrastructure\UserFetcher;
use App\Service\Infrastructure\ImageService;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisService;
use App\Service\S3Service;
use App\User\DataProvider\UserDataProvider;
use App\User\Domain\UserFactory;
use App\User\Repository\UserRepository;
use App\User\Service\UserEventService;
use App\User\Service\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class UnitTestBase extends TestCase
{
    public function getUserServiceMock(MockObject $userFetcher = null): UserService
    {
        return new UserService(
            publicDir: '/var/www/api/public',
            imageService: $this->createMock(ImageService::class),
            s3Service: $this->createMock(S3Service::class),
            dispatcher: $this->createMock(EventDispatcherInterface::class),
            userFactory: $this->createMock(UserFactory::class),
            userRepository: $this->createMock(UserRepository::class),
            userFetcher: $userFetcher ?? $this->createMock(UserFetcher::class),
            userDataProvider: $this->createMock(UserDataProvider::class),
            userEventService: $this->createMock(UserEventService::class),
            redisService: $this->createMock(RedisService::class),
            logger: $this->createMock(LogService::class),
        );
    }
}