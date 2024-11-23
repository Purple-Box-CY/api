<?php

namespace App\Command;

use Webmozart\Assert\Assert;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\User\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use App\Service\Utility\ProjectEmailAddressProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:translate',
    description: 'Translate',
)]
class TranslateCommand extends Command
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $io->ask('Count of votes', '1');
        $io->writeln($this->translator->trans('notification.vote', ['count' => $count]));
        return Command::SUCCESS;
    }
}
