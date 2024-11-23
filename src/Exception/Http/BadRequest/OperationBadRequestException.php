<?php

namespace App\Exception\Http\BadRequest;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OperationBadRequestException extends BadRequestException
{
    public function __construct(string $message = 'Bad operation', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
