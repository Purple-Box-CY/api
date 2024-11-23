<?php

namespace App\Command;

use Webmozart\Assert\Assert;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\User\Service\UserEmailService;
use App\User\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use App\Service\Utility\ProjectEmailAddressProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mail',
    description: 'Send test confirm email',
)]
class MailCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserEmailService $userEmailService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $io->ask(
            'userID',
            null,
            function (?string $input) {
                Assert::numeric($input, 'Id is invalid');

                return $input;
            }
        );

        $user = $this->userRepository->find($userId);
        if (!$user) {
            $io->error('User not found');
            return Command::INVALID;
        }

        $this->userEmailService->sendConfirmationEmail(
            user: $user,
            force: true,
        );

        return Command::SUCCESS;
    }
}
