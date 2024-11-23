<?php

namespace App\Exception\Http\AccessDenied;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObjectIsBlockedException extends NotFoundHttpException
{
    public function __construct(string $message = 'Object is blocked', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
