<?php

namespace App\Exception\Http\AccessDenied;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PasswordIsNotCorrectException extends AccessDeniedHttpException
{
    public function __construct(string $message = 'Password is not correct', \Throwable $previous = null, int $code = 403, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
