<?php

namespace App\User\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Infrastructure\LogService;
use App\User\DTO\Response\Me\ResponseMeMainInfoDTO;
use App\User\Service\UserService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class MeMainInfoProvider implements ProviderInterface
{
    public function __construct(
        private UserService            $userService,
        private LogService             $logger,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ResponseMeMainInfoDTO
    {
        try {
            $currentUser = $this->userService->getCurrentUser();
        } catch (Throwable $e) {
            $this->logger->error('Failed to get user',
                [
                    'method'            => __METHOD__,
                    'error_message'     => $e->getMessage(),
                    'error_stack_trace' => $e->getTrace(),
                ]);

            throw new NotFoundHttpException('Failed to get user');
        }

        $this->userService->checkUser($currentUser);

        $user = $this->userService->getUserById($currentUser->getId());
        if (!$user) {
            $this->logger->debug('Failed to get user by id',
                [
                    'user_id'  => $currentUser->getId(),
                    'user_uid' => $currentUser->getUid(),
                    'method'   => __METHOD__,
                ]);

            throw new NotFoundHttpException('Failed to get user '.$currentUser->getUid());
        }

        return ResponseMeMainInfoDTO::create(
            user: $user,
        );
    }
}
