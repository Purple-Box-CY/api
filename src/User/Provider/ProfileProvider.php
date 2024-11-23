<?php

namespace App\User\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Infrastructure\LogService;
use App\User\DTO\Response\ResponseUserInfoDTO;
use App\User\Service\UserService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ProfileProvider implements ProviderInterface
{

    public function __construct(
        private readonly LogService               $logger,
        private readonly UserService              $userService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ResponseUserInfoDTO
    {
        $username = $uriVariables['username'];
        try {
            try {
                $user = $this->userService->getUserByUsername($username);
                if (!$user) {
                    $user = $this->userService->getUserByUid($username);
                }
            } catch (Throwable $e) {
                $user = null;
            }
        } catch (InvalidArgumentException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error(
                'Failed to get user',
                [
                    'method'            => __METHOD__,
                    'error_message'     => $e->getMessage(),
                    'error_stack_trace' => $e->getTrace(),
                ],
            );

            throw new NotFoundHttpException('Failed to get user');
        }

        $this->userService->checkUser(
            user: $user,
            isCurrentUser: false,
            checkNeedRelogin: false,
        );

        $currentUser = $this->userService->getCurrentUser();
        if ($currentUser && $currentUser->getId() !== $user->getId()) {
            $this->userService->checkUser($currentUser);
        }


        return ResponseUserInfoDTO::create(
            user: $user,
        );
    }
}
