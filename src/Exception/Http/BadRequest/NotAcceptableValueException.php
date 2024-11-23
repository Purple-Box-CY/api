<?php

namespace App\Exception\Http\BadRequest;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NotAcceptableValueException extends HttpException
{
    public function __construct(string $message = 'Not acceptable value', int $code = 406, \Throwable $previous = null, array $headers = [])
    {
        parent::__construct(
            statusCode: $code,
            message: $message,
            previous: $previous,
            headers: $headers,
            code: $code,
        );
    }
}
