<?php

namespace App\Service\Infrastructure;

class ServerService
{
    public const ENV_PROD = 'prod';
    public const ENV_STAGE = 'stage';
    public const ENV_TEST = 'test';

    public function __construct(
        private string $appEnv,
    ) {
    }

    public function isStage(): bool
    {
        return in_array($this->appEnv, [self::ENV_STAGE, self::ENV_TEST]);
    }

    public function isProd(): bool
    {
        return $this->appEnv == self::ENV_PROD;
    }

    public function getAppEnv(): string
    {
        return $this->appEnv;
    }
}
