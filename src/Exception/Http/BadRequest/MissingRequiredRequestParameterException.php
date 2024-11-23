<?php

namespace App\Exception\Http\BadRequest;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class MissingRequiredRequestParameterException extends BadRequestException
{
    public function __construct(string $message = 'Missing required request parameter', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
