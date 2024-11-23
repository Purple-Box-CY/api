<?php

namespace App\Exception\Http\AccessDenied;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserIsNeedReloginException extends AccessDeniedHttpException
{
    public function __construct(string $message = 'Need relogin', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
