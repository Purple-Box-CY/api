<?php

namespace App\User\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\Infrastructure\LogService;
use App\User\Service\UserService;
use App\Security\Domain\UserFetcherInterface;
use App\User\Domain\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\User\DTO\Request\RequestUpdatePasswordDTO;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UpdatePasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserFetcherInterface $userFetcher,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LogService $logger,
    ) {
    }

    /**
     * @param RequestUpdatePasswordDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $user = $this->userService->getUserById($user->getId());

        $hashedPassword = $this->passwordHasher->hash($user, $data->oldPassword);
        if ($hashedPassword !== $user->getPassword()) {
            $this->logger->debug('Old password is wrong', [
                'user_uid' => $user->getUid(),
            ]);
            throw new BadRequestHttpException('Old password is wrong');
        }
        $this->userService->changePassword($user, $data->newPassword);

        $this->userService->saveUser($user);

        return new JsonResponse(
            status: Response::HTTP_NO_CONTENT
        );
    }
}
