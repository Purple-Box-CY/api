<?php

namespace App\User\Processor;

use App\User\Service\UserService;
use ApiPlatform\Metadata\Operation;
use App\User\Service\UserEmailService;
use ApiPlatform\State\ProcessorInterface;
use App\Security\Domain\UserFetcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\User\DTO\Request\RequestResendConfirmationDTO;

final class ResendConfirmationEmailProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly UserEmailService $userEmailService,
        private readonly UserService $userService,
    ) {
    }

    /**
     * @param RequestResendConfirmationDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        try {
            $user = $this->userFetcher->getAuthUser();
        } catch (\Throwable $e) {
            $user = $this->userService->getUserByEmail($data->email);
        }

        if (!$user) {
            return new JsonResponse(
                data: ['error' => 'User not found.'],
                status: Response::HTTP_NOT_FOUND,
            );
        }

        $user = $this->userService->getUserById($user->getId());
        $isSent = $this->userEmailService->sendConfirmationEmail($user);

        if (!$isSent) {
            return new JsonResponse(
                data: ['error' => 'Too many requests. Please wait'],
                status: Response::HTTP_REQUEST_TIMEOUT,
            );
        }

        return new JsonResponse(
            status: Response::HTTP_ACCEPTED
        );
    }
}
