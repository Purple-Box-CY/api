<?php

namespace App\User\Provider;

use App\Exception\Http\AccessDenied\ObjectIsBlockedException;
use App\Exception\Http\AccessDenied\UserIsDeletedException;
use App\Exception\Http\NotFound\UserNotFoundHttpException;
use App\Service\Infrastructure\LogService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\Utility\MomentHelper;
use App\User\DTO\Response\Me\ResponseMeInfoDTO;
use App\User\Entity\User;
use App\User\Service\UserService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class MeInfoProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserService          $userService,
        private readonly RedisService         $redisService,
        private readonly LogService           $logger,
    ) {
        $this->redisService->setPrefix(RedisKeys::PREFIX_USER);
    }

    public function provide(
        Operation $operation,
        array     $uriVariables = [],
        array     $context = []
    ): ResponseMeInfoDTO|\stdClass {
        try {
            /** @var User $currentUser */
            $currentUser = $this->userService->getCurrentUser();
        } catch (InvalidArgumentException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error('Failed to get user',
                [
                    'method'            => __METHOD__,
                    'error_message'     => $e->getMessage(),
                    'error_stack_trace' => $e->getTrace(),
                ]);

            throw new NotFoundHttpException('Failed to get user');
        }

        try {
            $this->userService->checkUser($currentUser);
        } catch (ObjectIsBlockedException|UserIsDeletedException $e) {
            //nothing
        }

        $redisKey = sprintf(RedisKeys::KEY_USER_INFO, $currentUser->getUid());
        /** @var ResponseMeInfoDTO $userInfoResponse */
        $userInfoResponse = $this->redisService->getObject($redisKey);
        if ($userInfoResponse) {
            return $userInfoResponse;
        }

        $userObject = $this->userService->getUserByUid($currentUser->getUid(), false);
        if (!$userObject) {
            $this->logger->debug('Failed to get User by uid. Maybe the user has been deleted',
                [
                    'user_id'  => $currentUser->getId(),
                    'user_uid' => $currentUser->getUid(),
                    'method'   => __METHOD__,
                ]);

            throw new UserNotFoundHttpException();
        }
        $userInfoResponse = ResponseMeInfoDTO::create($userObject);
        $this->redisService->setObject($redisKey, $userInfoResponse, MomentHelper::SECONDS_HOUR);

        return $userInfoResponse;
    }
}
