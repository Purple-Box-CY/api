<?php

namespace App\Exception\Http\AccessDenied;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccessDeniedQuotaLimitedException extends AccessDeniedHttpException
{
    public function __construct(string $message = 'Content limit is exceeded', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
