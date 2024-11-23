<?php

namespace App\User\DTO\Response;

class ResponseAuthDTO
{
    public function __construct(
        public string $token = '',
        public string $refreshToken = '',
        public string $streamToken = '',
        public string $userType = 'user',
    ) {
    }
}