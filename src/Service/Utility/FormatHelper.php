<?php

namespace App\Service\Utility;

use Symfony\Component\Uid\Ulid;

class FormatHelper
{
    public static function isValidUid(mixed $uid): string
    {
        try {
            return is_string($uid) && Ulid::isValid($uid);
        } catch (\Exception $e) {
            return false;
        }
    }
}