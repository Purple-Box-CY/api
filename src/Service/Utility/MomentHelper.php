<?php

namespace App\Service\Utility;

class MomentHelper
{
    public const SECONDS_YEAR      = 31104000;
    public const SECONDS_MONTH     = 2592000;
    public const SECONDS_WEEK      = 604800;
    public const SECONDS_2_DAYS    = 172800;
    public const SECONDS_DAY       = 86400;
    public const SECONDS_HALF_DAY  = 43200;
    public const SECONDS_HOUR      = 3600;
    public const SECONDS_HALF_HOUR = 1800;
    public const SECONDS_20_MINUTES = 1200;
    public const SECONDS_15_MINUTES = 900;
    public const SECONDS_5_MINUTES = 300;
    public const SECONDS_MINUTE    = 60;
    public const SECONDS_5_SEC    = 5;

    public const MILLISECONDS_SECOND = 1000;

    public const MICROSECONDS_MINUTE      = self::SECONDS_MINUTE * self::MICROSECONDS_SECOND;
    public const MICROSECONDS_SECOND      = 1000000;
    public const MICROSECONDS_MILLISECOND = 1000;

    public static function getSeconds(): int
    {
        return (int)microtime(true);
    }

    public static function getMilliseconds(): int
    {
        return (int)(microtime(true) * 1000);
    }

    public static function getMicroseconds(): int
    {
        return (int)(microtime(true) * 1000000);
    }

    public static function getNanoseconds(): int
    {
        return (int)(microtime(true) * 1000000000);
    }

    public static function convertSecondsToMs(int $seconds): int
    {
        return $seconds * 1000;
    }

    public static function convertMsToSeconds(int $milliseconds): float
    {
        return $milliseconds / 1000;
    }
}