<?php

namespace App\User\Processor\Me;

use App\ApiDTO\Request\Base\EmptyRequest;
use App\Exception\Http\RunTimeError\RunTimeHttpException;
use App\Exception\Http\Conflict\UserAlreadyDeletedHttpException;
use App\Exception\Http\NotFound\FailedToGetUserHttpException;
use App\Service\Infrastructure\LogService;
use App\User\Entity\User;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class DeleteUserProcessor implements ProcessorInterface
{
    public function __construct(
        private UserService $userService,
        private LogService  $logger,
    ) {
    }

    /**
     * @param EmptyRequest $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $user = $this->userService->getCurrentUser();
        $user = $this->userService->getUserById($user->getId());

        if (!$user || !($user instanceof User)) {
            throw new FailedToGetUserHttpException();
        }

        if ($user->isDeleted()) {
            throw new UserAlreadyDeletedHttpException();
        }

        try {
            $this->userService->deleteUser($user);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to delete user',
                [
                    'method' => __METHOD__,
                    'error'  => $e->getMessage(),
                ]);

            throw new RunTimeHttpException('Failed to delete user');
        }

        return new JsonResponse(
            data: [],
            status: Response::HTTP_ACCEPTED
        );
    }
}
