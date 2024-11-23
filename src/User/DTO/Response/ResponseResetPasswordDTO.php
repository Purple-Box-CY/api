<?php

namespace App\User\DTO\Response;

class ResponseResetPasswordDTO
{
    public function __construct(
        public string $message = ''
    ) {
    }
}