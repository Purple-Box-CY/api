<?php

namespace App\User\Provider;

use App\Security\EmailVerifier;
use App\Service\Utility\FormatHelper;
use Symfony\Component\Uid\Ulid;
use ApiPlatform\Metadata\Operation;
use App\User\Repository\UserRepository;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\ProcessorInterface;
use App\User\DTO\Request\RequestVerifyDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

final class VerifyProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EmailVerifier $emailVerifier,
        private readonly RequestStack $requestStack,
        private readonly string $webProjectDomain,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RedirectResponse
    {
        $uid = $context['filters']['uid'] ?? null;
        $contentId = $context['filters']['contentId'] ?? null;

        if (!$uid) {
            return $this->errorRedirect('No uid provided');
        }

        if (!FormatHelper::isValidUid($uid)) {
            return $this->errorRedirect('Uid is not valid');
        }

        $user = $this->userRepository->findOneBy([
            'ulid' => new Ulid($uid)
        ]);

        if (null === $user) {
            return $this->errorRedirect('User not found');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($this->requestStack->getCurrentRequest()->getUri(), $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            return $this->errorRedirect('Wrong sign. ' . $exception->getMessage(), $user->getEmail());
        }

        $url = sprintf('%s/?confirmationSuccess=true', $this->webProjectDomain);
        if ($contentId) {
            $url = sprintf('%s&c_uid=%s', $url, $contentId);
        }
        return new RedirectResponse($url);
    }

    private function errorRedirect(string $error, string $email = ''): RedirectResponse
    {
        return new RedirectResponse($this->webProjectDomain . "/user/confirmation/failed?error=$error&email=$email");
    }
}
