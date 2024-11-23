<?php

namespace App\Exception\Http\NotFound;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserNotFoundHttpException extends NotFoundHttpException
{
    public function __construct(string $message = 'User not found', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
