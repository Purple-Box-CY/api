<?php

namespace App\User\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final readonly class LogoutProcessor implements ProcessorInterface
{
    public function __construct(
        private UserService      $userService,
        private RequestStack     $requestStack,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $currentUser = $this->userService->getCurrentUser();

        $this->userService->checkUser(
            user: $currentUser,
            checkNeedRelogin: false,
        );

        return new JsonResponse(
            status: Response::HTTP_ACCEPTED,
        );
    }
}
