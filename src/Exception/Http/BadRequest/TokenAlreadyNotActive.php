<?php

namespace App\Exception\Http\BadRequest;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TokenAlreadyNotActive extends BadRequestException
{
    public function __construct(string $message = 'Token already not active', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}