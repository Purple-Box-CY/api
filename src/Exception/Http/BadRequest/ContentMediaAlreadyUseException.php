<?php

namespace App\Exception\Http\BadRequest;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ContentMediaAlreadyUseException extends BadRequestException
{
    public function __construct(string $message = 'Content media already use', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
