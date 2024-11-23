<?php

namespace App\Command\Tech;

use App\Service\Infrastructure\LogService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tech:test',
    description: 'Test',
)]
class TestCommand extends Command
{
    public function __construct(
        private LogService $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('hello world');
        $this->logger->error('Test',
            [
                'var1' => 'value1',
                'var2' => 'value2',
                'var3' => 'value3',
            ]);

        throw new \RuntimeException('Example exception.');

        return Command::FAILURE;
    }
}
