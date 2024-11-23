<?php

declare(strict_types=1);

namespace App\User\Command;

use App\User\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create new user by email and password',
)]
class UserCreateCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask(
            'email',
            null,
            function (?string $input) {
                Assert::email($input, 'Email is invalid');

                return $input;
            }
        );

        $username = $io->ask(
            'username',
            null,
            function (?string $input) {
                Assert::notEmpty($input, 'Name is invalid');

                return $input;
            }
        );

        $password = $io->askHidden(
            'password',
            function (?string $input) {
                Assert::notEmpty($input, 'Password cannot be empty');

                return $input;
            }
        );

        $user = $this->userService->getUserByEmail($email);
        if ($user) {
            $io->error('User by this email already exists');
            return Command::FAILURE;
        }

        $user = $this->userService->getUserByUsername($username);
        if ($user) {
            $io->error('User by this username already exists');
            return Command::FAILURE;
        }

        $user = $this->userService->createUser($email, $username, $password);

        $io->info('Success. User uid - '.$user->getUid());

        return Command::SUCCESS;
    }
}
