<?php

namespace App\Exception\Http\AccessDenied;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserIsNotApprovedException extends AccessDeniedHttpException
{
    public function __construct(string $message = 'User is not approved', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
