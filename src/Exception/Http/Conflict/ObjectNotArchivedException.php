<?php

namespace App\Exception\Http\Conflict;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ObjectNotArchivedException extends BadRequestException
{
    public function __construct(string $message = 'Object is not archived', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
