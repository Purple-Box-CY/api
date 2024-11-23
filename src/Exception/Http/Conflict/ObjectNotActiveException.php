<?php

namespace App\Exception\Http\Conflict;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ObjectNotActiveException extends BadRequestException
{
    public function __construct(string $message = 'Object is not active', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
