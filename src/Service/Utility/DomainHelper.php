<?php

namespace App\Service\Utility;

class DomainHelper
{
    public static function getApiProjectDomain(): string
    {
        return $_ENV['API_PROJECT_DOMAIN'] ?? '';
    }

    public static function getCmsProjectDomain(): ?string
    {
        return $_ENV['CMS_PROJECT_DOMAIN'] ?? '';
    }

    public static function getCdnDomain(): ?string
    {
        return $_ENV['CDN_DOMAIN'] ?? '';
    }
    public static function getWebProjectDomain(): string
    {
        return $_ENV['WEB_PROJECT_DOMAIN'] ?? '';
    }

    public static function cdnIsEnabled(): bool
    {
        return (bool)($_ENV['CDN_ENABLED'] ?? false);
    }
}