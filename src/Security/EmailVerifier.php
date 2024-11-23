<?php

namespace App\Security;

use App\Security\Domain\AuthUserInterface;
use App\Service\MailService;
use App\User\DataProvider\UserDataProvider;
use App\User\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailService                $mailService,
        private UserDataProvider           $userDataProvider,
    ) {
    }

    public function sendEmailConfirmation(
        string            $verifyEmailRouteName,
        AuthUserInterface $user,
        TemplatedEmail    $email,
        array             $context = [],
    ): void {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getUid(),
            $user->getEmail(),
            ['uid' => $user->getUid(), 'contentId' => $context['contentId']],
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailService->sendConfirmationRegistration($user, $context);
        //$this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(string $uri, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($uri, $user->getUid(), $user->getEmail());

        if ($user->isVerified()) {
            return;
        }

        $user->setIsVerified(true);

        $this->userDataProvider->saveUser($user);
    }
}
