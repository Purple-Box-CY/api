<?php

namespace App\Service\Infrastructure;

use Psr\Log\LoggerInterface;

class LogService implements LoggerInterface
{
    private const PREFIX = 'API. ';

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->error($message, $context);
    }

    public function errorSlack(string|\Stringable $message, array $context = [], ?string $slackChannel = null): void
    {
        $message = self::PREFIX.$message;
        $this->logger->error($message, $context);
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->emergency($message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->alert($message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->critical($message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->warning($message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->notice($message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->info($message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->debug($message, $context);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $message = self::PREFIX.$message;
        $this->logger->log($level, $message, $context);
    }
}
