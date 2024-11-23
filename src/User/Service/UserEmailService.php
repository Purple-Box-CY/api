<?php

namespace App\User\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use App\Security\EmailVerifier;
use App\User\Entity\User;
use App\Service\Utility\MomentHelper;
use App\User\Repository\UserRepository;
use App\Service\Infrastructure\RedisKeys;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Service\Infrastructure\RedisService;
use App\Service\Utility\ProjectEmailAddressProvider;

class UserEmailService
{
    public function __construct(
        private readonly EmailVerifier               $emailVerifier,
        private readonly ProjectEmailAddressProvider $emailAddressProvider,
        private readonly UserRepository              $userRepository,
        private readonly RedisService                $redisService,
        private readonly SluggerInterface            $slugger,
    ) {
        $this->redisService->setPrefix(RedisKeys::PREFIX_EMAIL_CONFIRMATION);
    }

    public function sendConfirmationEmail(User $user, ?string $contentId = null, bool $force = false): bool
    {
        if (!$force && $this->redisService->get($user->getId())) {
            return false;
        }
        $this->emailVerifier->sendEmailConfirmation(
            verifyEmailRouteName: 'app_verify_email',
            user: $user,
            email: (new TemplatedEmail())
                ->from($this->emailAddressProvider->provide())
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->context(['name' => $user->getPrintName()])
                ->htmlTemplate('registration/confirmation_email.html.twig'),
            context: ['contentId' => $contentId],
        );
        $this->redisService->set($user->getId(), 'sent', MomentHelper::SECONDS_MINUTE);

        return true;
    }


    public function generateUsernameByEmail(string $email): string
    {
        [$username, $domain] = explode('@', $email);
        $username = $this->slugger->slug($username);
        $existingUser = $this->userRepository->findOneBy(['username' => $username]);
        if ($existingUser) {
            $username = sprintf('%s-%s', $username, substr(base_convert(md5(time()), 16, 32), 0, 11));
        }

        return $username;
    }

    public function generatePassword(): string
    {
        return substr(base_convert(md5(time()), 16, 32), 0, 10);
    }
}
