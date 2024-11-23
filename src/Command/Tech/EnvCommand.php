<?php

namespace App\Command\Tech;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:tech:env',
    description: 'Check env',
)]
class EnvCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln($this->kernel->getEnvironment());
        return Command::SUCCESS;
    }
}
