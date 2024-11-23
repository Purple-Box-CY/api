<?php

namespace App\Exception\Http\BadRequest;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class BadFormatRequestException extends BadRequestException
{
    public function __construct(string $message = 'Bad format request', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
