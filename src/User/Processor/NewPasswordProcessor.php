<?php

namespace App\User\Processor;

use App\User\Entity\User;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Service\UserService;
use App\User\DTO\Request\RequestNewPasswordDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

final class NewPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
    ) {}

    /**
     * @param RequestNewPasswordDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $sign = $data->sign;

        try {
            /**
             * @var User $user
             */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($sign);
        } catch (ResetPasswordExceptionInterface $exception) {
            throw new NotFoundHttpException('Reset password request not found');
        }

        // A password reset token should be used only once, remove it.
        $this->resetPasswordHelper->removeResetRequest($sign);

        $this->userService->changePassword($user, $data->password);

        $this->userService->saveUser($user);

        return new JsonResponse(
            status: Response::HTTP_NO_CONTENT
        );
    }
}
