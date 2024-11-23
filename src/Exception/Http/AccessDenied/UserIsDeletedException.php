<?php

namespace App\Exception\Http\AccessDenied;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserIsDeletedException extends NotFoundHttpException
{
    public function __construct(string $message = 'User is deleted', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
