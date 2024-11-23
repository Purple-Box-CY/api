<?php

namespace App\User\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

class RedirectToResetPasswordProvider implements ProviderInterface
{
    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly string $webProjectDomain,
    ) {}

    /**
     * @throws ResetPasswordExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RedirectResponse|JsonResponse
    {
        $sign = $uriVariables['sign'];
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($sign);
        } catch (ResetPasswordExceptionInterface $exception) {
            return new RedirectResponse($this->webProjectDomain . "/user/reset-password/failed?error=Failed to find reset password request");
        }

        return new RedirectResponse($this->webProjectDomain . '/user/new-password?sign=' . $sign);
    }
}
