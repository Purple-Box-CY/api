<?php

namespace App\User\Provider\Settings;

use App\Service\Infrastructure\LogService;
use App\User\Entity\User;
use App\User\Service\UserService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Exception\Http\BadRequest\MissingRequiredRequestParameterException;

class SettingsNotificationsUnsubscribeProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly LogService  $logger,
        private readonly string      $webProjectDomain,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $hash = $context['filters']['id'] ?? null;
        if (!$hash) {
            throw new MissingRequiredRequestParameterException('"id" is required');
        }

        try {
            $email = base64_decode($hash);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to decode email hash for subscribe',
                [
                    'error' => $e->getMessage(),
                    'hash'  => $hash,
                ]);

            return new RedirectResponse($this->getRedirectLinkFail());
        }

        try {
            $user = $this->userService->getUserByEmail($email);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to get user by email for subscribe',
                [
                    'error' => $e->getMessage(),
                    'hash'  => $hash,
                    'email' => $email,
                ]);

            return new RedirectResponse($this->getRedirectLinkFail());
        }

        if (!$user) {
            $this->logger->warning('Failed to get user by email for subscribe',
                [
                    'email' => $email,
                    'hash'  => $hash,
                ]);

            return new RedirectResponse($this->getRedirectLinkFail());
        }

        $isUnsubscribed = $user->isUnsubscribed();
        if ($isUnsubscribed) {
            return new RedirectResponse(
                $this->getRedirectUnsubscribedLink($user, 'already')
            );
        }

        $result = $this->userService->unsubscribeFromNotifications($user);

        return new RedirectResponse(
            $this->getRedirectUnsubscribedLink($user, $result ? 'success' : 'fail')
        );
    }

    private function getRedirectUnsubscribedLink(User $user, string $status): string
    {
        return sprintf(
            '%s/profile/%s?unsubscribeStatus=%s',
            $this->webProjectDomain,
            $user->getUid(),
            $status,
        );
    }

    private function getRedirectLinkFail(): string
    {
        return sprintf(
            '%s?unsubscribeStatus=%s',
            $this->webProjectDomain,
            'fail',
        );
    }
}