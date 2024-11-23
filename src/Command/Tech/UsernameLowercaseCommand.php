<?php

namespace App\Command\Tech;

use App\User\Repository\UserRepository;
use App\User\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tech:username-lowercase',
    description: 'Update username and email to lowercase',
)]
class UsernameLowercaseCommand extends Command
{
    public function __construct(
        private UserService    $userService,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();
        $io->writeln('Count: '.count($users));
        foreach ($users as $user) {
            $newUsername = mb_strtolower($user->getUsername());

            if ($newUsername !== $user->getUsername()) {
                $message = sprintf('%s --> %s', $user->getUsername(), $newUsername);
                $io->warning($message);
                $user->setUsername($newUsername);
                try {
                    $this->userService->saveUser($user);
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                }
            }

            $newEmail = mb_strtolower($user->getEmail());
            if ($newEmail !== $user->getEmail()) {
                $message = sprintf('%s --> %s', $user->getEmail(), $newEmail);
                $io->warning($message);
                $user->setEmail($newEmail);
                try {
                    $this->userService->saveUser($user);
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }
}
