<?php

namespace App\Service;

class RequestService
{
    public const  HEADER_USER_ID      = 'User-Id';
    public const  HEADER_USER_COUNTRY = 'User-Country';
    public const  HEADER_USER_AGENT   = 'User-Agent';
    private const MAIN_NUM            = 1234567;
    private const MAX_KEY             = 99999999999;

    public function __construct()
    {
    }

    public function generateUserId(int $key = null): string
    {
        if (!$key) {
            $key = rand(1,self::MAX_KEY);
        }

        return base64_encode(self::MAIN_NUM*$key);
    }
}