<?php

namespace App\Exception\Http\NotFound;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObjectNotActiveHttpException extends NotFoundHttpException
{
    public function __construct(string $message = 'Object not active', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
