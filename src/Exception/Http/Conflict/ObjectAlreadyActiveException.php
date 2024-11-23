<?php

namespace App\Exception\Http\Conflict;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ObjectAlreadyActiveException extends BadRequestException
{
    public function __construct(string $message = 'Object is already active', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
