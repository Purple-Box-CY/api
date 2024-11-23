<?php

namespace App\Exception\Http\NotFound;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ObjectIsDeletedHttpException extends NotFoundHttpException
{
    public function __construct(string $message = 'Object is deleted', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
