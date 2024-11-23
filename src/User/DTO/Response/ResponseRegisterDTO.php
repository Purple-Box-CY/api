<?php

namespace App\User\DTO\Response;

class ResponseRegisterDTO
{
    public function __construct(
        public string $message = '',
        public string $token = '',
        public string $refreshToken = '',
        public string $streamToken = '',
        public string $userType = 'user',
        public string $uid = '',
        public string $authHash = '',
    ) {
    }
}