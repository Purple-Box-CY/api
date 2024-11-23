<?php

namespace App\Service\Infrastructure\Traits;

use App\Service\Infrastructure\LogService;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Optional:
 * @property SymfonyStyle $io
 *
 * Required:
 * @property LogService   logger
 * @const  string self::LOG_PREFIX
 */
trait LogTrait
{
    private function logInfo(string $message, array $context = []): void
    {
        if (property_exists($this, 'io')) {
            $this->io->writeln($message);
        }
        $this->logger->info(self::LOG_PREFIX.$message, $context);
    }

    private function logError(string $message, array $context = []): void
    {
        $message = addslashes($message);
        if (property_exists($this, 'io')) {
            $this->io->error($message);
        }
        $this->logger->error(self::LOG_PREFIX.$message, $context);
    }

    private function logWarning(string $message, array $context = []): void
    {
        $message = addslashes($message);
        if (property_exists($this, 'io')) {
            $this->io->warning($message);
        }
        $this->logger->warning(self::LOG_PREFIX.$message, $context);
    }

    private function logDebug(string $message, array $context = []): void
    {
        $message = addslashes($message);
        if (property_exists($this, 'io')) {
            $this->io->info($message);
        }
        $this->logger->debug(self::LOG_PREFIX.$message, $context);
    }
}